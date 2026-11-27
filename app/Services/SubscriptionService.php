<?php

namespace App\Services;

use App\Models\Provider;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class SubscriptionService
{
    /** @return Collection<int, Subscription> */
    public static function getAllSubscriptions(): Collection
    {
        return Subscription::all();
    }

    public static function getSubscriptionById(int|string $id): ?Subscription
    {
        return Subscription::find($id);
    }

    /** @param array<string, mixed> $data */
    public static function createSubscription(array $data): Subscription
    {
        return DB::transaction(function () use ($data): Subscription {
            $data = self::validateSubscription($data);

            if (($data['status'] ?? 'pending') !== 'pending') {
                throw ValidationException::withMessages([
                    'status' => 'New subscriptions must be pending.',
                ]);
            }

            self::requireActivePlan((int) $data['subscription_plan_id']);
            $subscription = Subscription::create($data);

            if (! $subscription->exists) {
                throw new RuntimeException('The subscription could not be created.');
            }

            return $subscription;
        }, 3);
    }

    /** @param array<string, mixed> $data */
    public static function updateSubscription(int|string $id, array $data): ?Subscription
    {
        return DB::transaction(function () use ($id, $data): ?Subscription {
            $subscription = Subscription::whereKey($id)->lockForUpdate()->first();

            if (! $subscription) {
                return null;
            }

            $data = self::validateSubscription(array_replace($subscription->only([
                'provider_id', 'subscription_plan_id', 'status', 'started_at', 'ends_at', 'provider_payment_id',
            ]), $data));

            if ($data['status'] === 'active' && $subscription->status !== 'active') {
                throw ValidationException::withMessages([
                    'status' => 'Subscriptions must be activated by the payment integration.',
                ]);
            }

            if ((int) $data['subscription_plan_id'] !== (int) $subscription->subscription_plan_id) {
                self::requireActivePlan((int) $data['subscription_plan_id']);
            }

            if ($data['status'] === 'active') {
                self::ensureSingleActiveSubscription($subscription, (int) $data['provider_id']);
            }

            if (! $subscription->update($data)) {
                throw new RuntimeException('The subscription could not be updated.');
            }

            return $subscription;
        }, 3);
    }

    public static function deleteSubscription(int|string $id): ?Subscription
    {
        $subscription = Subscription::find($id);

        if ($subscription && ! $subscription->delete()) {
            throw new RuntimeException('The subscription could not be deleted.');
        }

        return $subscription;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private static function validateSubscription(array $data): array
    {
        $data = Validator::make($data, [
            'provider_id' => ['required', 'integer', 'exists:providers,id'],
            'subscription_plan_id' => ['required', 'integer', 'exists:subscription_plans,id'],
            'status' => ['sometimes', 'required', Rule::in(['pending', 'active', 'expired', 'cancelled', 'failed'])],
            'started_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
            'provider_payment_id' => ['nullable', 'string', 'max:191'],
        ])->validate();

        if (isset($data['started_at'], $data['ends_at']) && Carbon::parse($data['ends_at'])->lessThanOrEqualTo(Carbon::parse($data['started_at']))) {
            throw ValidationException::withMessages([
                'ends_at' => 'The end date must be after the start date.',
            ]);
        }

        return $data;
    }

    private static function requireActivePlan(int $planId): void
    {
        $plan = SubscriptionPlan::whereKey($planId)->lockForUpdate()->first();

        if (! $plan || ! $plan->active) {
            throw ValidationException::withMessages([
                'subscription_plan_id' => 'The subscription plan must be active.',
            ]);
        }
    }

    private static function ensureSingleActiveSubscription(Subscription $subscription, int $providerId): void
    {
        $providerIds = array_values(array_unique([(int) $subscription->provider_id, $providerId]));
        $providers = Provider::whereIn('id', $providerIds)->orderBy('id')->lockForUpdate()->get(['id']);

        if ($providers->count() !== count($providerIds)) {
            throw ValidationException::withMessages([
                'provider_id' => 'The selected provider is invalid.',
            ]);
        }

        $existing = Subscription::where('provider_id', $providerId)
            ->where('status', 'active')
            ->where('id', '!=', $subscription->id)
            ->lockForUpdate()
            ->first();

        if ($existing) {
            throw ValidationException::withMessages([
                'provider_id' => 'The provider already has an active subscription.',
            ]);
        }
    }
}
