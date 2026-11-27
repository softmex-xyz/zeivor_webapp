<?php

namespace Tests\Feature\Service;

use App\Models\Subscription;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Tests\TestCase;

class SubscriptionServiceTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_creates_lists_and_finds_pending_subscriptions(): void
    {
        $data = $this->subscriptionData();

        $subscription = SubscriptionService::createSubscription([
            ...$data,
            'started_at' => '2026-11-27 12:00:00',
            'ends_at' => '2026-12-27 12:00:00',
            'provider_payment_id' => 'payment-subscription-id',
        ]);

        $this->assertDatabaseHas('subscriptions', ['id' => $subscription->id, ...$data, 'status' => 'pending']);
        $this->assertSame($data['provider_id'], $subscription->provider->id);
        $this->assertSame($data['subscription_plan_id'], $subscription->plan->id);
        $this->assertSame('2026-11-27 12:00:00', $subscription->started_at->toDateTimeString());
        $this->assertSame('2026-12-27 12:00:00', $subscription->ends_at->toDateTimeString());
        $this->assertCount(1, SubscriptionService::getAllSubscriptions());
        $this->assertSame($subscription->id, SubscriptionService::getSubscriptionById($subscription->id)->id);
    }

    public function test_creates_multiple_pending_subscriptions_for_the_same_provider(): void
    {
        $data = $this->subscriptionData();

        SubscriptionService::createSubscription($data);
        SubscriptionService::createSubscription($data);

        $this->assertDatabaseCount('subscriptions', 2);
    }

    public function test_updates_a_subscription_and_clears_optional_fields(): void
    {
        $subscription = SubscriptionService::createSubscription([
            ...$this->subscriptionData(),
            'started_at' => '2026-11-27 12:00:00',
            'ends_at' => '2026-12-27 12:00:00',
            'provider_payment_id' => 'payment-id',
        ]);

        $updated = SubscriptionService::updateSubscription($subscription->id, [
            'status' => 'cancelled',
            'provider_payment_id' => null,
        ]);

        $this->assertSame($subscription->id, $updated->id);
        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'status' => 'cancelled',
            'provider_payment_id' => null,
        ]);
    }

    public function test_rejects_invalid_subscription_data_and_date_ranges(): void
    {
        $data = $this->subscriptionData();

        try {
            SubscriptionService::createSubscription([
                'provider_id' => 99999,
                'subscription_plan_id' => 99999,
                'status' => 'invalid',
                'started_at' => 'not a date',
                'provider_payment_id' => str_repeat('x', 192),
            ]);
            $this->fail('Invalid subscription data must be rejected.');
        } catch (ValidationException $exception) {
            foreach (['provider_id', 'subscription_plan_id', 'status', 'started_at', 'provider_payment_id'] as $field) {
                $this->assertArrayHasKey($field, $exception->errors());
            }
        }

        try {
            SubscriptionService::createSubscription([
                ...$data,
                'started_at' => '2026-11-27 12:00:00',
                'ends_at' => '2026-11-27 12:00:00',
            ]);
            $this->fail('An end date not after the start must be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('ends_at', $exception->errors());
        }

        $this->assertDatabaseCount('subscriptions', 0);
    }

    public function test_partial_date_update_is_compared_to_the_existing_date(): void
    {
        $subscription = SubscriptionService::createSubscription([
            ...$this->subscriptionData(),
            'started_at' => '2026-11-27 12:00:00',
            'ends_at' => '2026-12-27 12:00:00',
        ]);

        try {
            SubscriptionService::updateSubscription($subscription->id, ['started_at' => '2027-01-01 12:00:00']);
            $this->fail('A start date after the existing end must be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('ends_at', $exception->errors());
        }

        $this->assertDatabaseHas('subscriptions', ['id' => $subscription->id, 'started_at' => '2026-11-27 12:00:00']);
    }

    public function test_rejects_non_pending_creation_and_manual_activation(): void
    {
        $data = $this->subscriptionData();

        foreach (['active', 'cancelled'] as $status) {
            try {
                SubscriptionService::createSubscription([...$data, 'status' => $status]);
                $this->fail('A new subscription must be pending.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('status', $exception->errors());
            }
        }

        $subscription = SubscriptionService::createSubscription($data);

        try {
            SubscriptionService::updateSubscription($subscription->id, ['status' => 'active']);
            $this->fail('Manual activation must be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('status', $exception->errors());
        }

        $this->assertDatabaseHas('subscriptions', ['id' => $subscription->id, 'status' => 'pending']);
    }

    public function test_rejects_new_and_reassigned_inactive_plans_but_allows_status_changes(): void
    {
        $data = $this->subscriptionData();
        $inactivePlanId = $this->createPlan(active: false);

        try {
            SubscriptionService::createSubscription([...$data, 'subscription_plan_id' => $inactivePlanId]);
            $this->fail('An inactive plan must be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('subscription_plan_id', $exception->errors());
        }

        $subscription = SubscriptionService::createSubscription($data);
        try {
            SubscriptionService::updateSubscription($subscription->id, ['subscription_plan_id' => $inactivePlanId]);
            $this->fail('An inactive replacement plan must be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('subscription_plan_id', $exception->errors());
        }

        DB::table('subscription_plans')->where('id', $data['subscription_plan_id'])->update(['active' => false]);
        SubscriptionService::updateSubscription($subscription->id, ['status' => 'cancelled']);

        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'subscription_plan_id' => $data['subscription_plan_id'],
            'status' => 'cancelled',
        ]);
    }

    public function test_rejects_moving_an_active_subscription_to_a_provider_with_an_active_subscription(): void
    {
        $first = $this->subscriptionData();
        $second = $this->subscriptionData();
        $id = DB::table('subscriptions')->insertGetId([...$first, 'status' => 'active']);
        DB::table('subscriptions')->insert([...$second, 'status' => 'active']);

        try {
            SubscriptionService::updateSubscription($id, ['provider_id' => $second['provider_id']]);
            $this->fail('A second active subscription must be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('provider_id', $exception->errors());
        }

        $this->assertDatabaseHas('subscriptions', ['id' => $id, 'provider_id' => $first['provider_id'], 'status' => 'active']);
    }

    public function test_allows_moving_an_existing_active_subscription_to_a_provider_without_one(): void
    {
        $first = $this->subscriptionData();
        $second = $this->subscriptionData();
        $id = DB::table('subscriptions')->insertGetId([...$first, 'status' => 'active']);

        SubscriptionService::updateSubscription($id, ['provider_id' => $second['provider_id']]);

        $this->assertDatabaseHas('subscriptions', ['id' => $id, 'provider_id' => $second['provider_id'], 'status' => 'active']);
    }

    public function test_existing_inactive_plan_allows_active_subscription_status_changes(): void
    {
        $data = $this->subscriptionData();
        $id = DB::table('subscriptions')->insertGetId([...$data, 'status' => 'active']);
        DB::table('subscription_plans')->where('id', $data['subscription_plan_id'])->update(['active' => false]);

        SubscriptionService::updateSubscription($id, ['status' => 'cancelled']);

        $this->assertDatabaseHas('subscriptions', ['id' => $id, 'status' => 'cancelled', 'subscription_plan_id' => $data['subscription_plan_id']]);
    }

    public function test_allows_reassignment_to_an_active_plan(): void
    {
        $subscription = SubscriptionService::createSubscription($this->subscriptionData());
        $planId = $this->createPlan();

        SubscriptionService::updateSubscription($subscription->id, ['subscription_plan_id' => $planId]);

        $this->assertDatabaseHas('subscriptions', ['id' => $subscription->id, 'subscription_plan_id' => $planId]);
    }

    public function test_deletes_a_subscription_and_returns_null_for_missing_records(): void
    {
        $subscription = SubscriptionService::createSubscription($this->subscriptionData());

        $deleted = SubscriptionService::deleteSubscription($subscription->id);

        $this->assertSame($subscription->id, $deleted->id);
        $this->assertDatabaseMissing('subscriptions', ['id' => $subscription->id]);
        $this->assertNull(SubscriptionService::getSubscriptionById($subscription->id));
        $this->assertNull(SubscriptionService::updateSubscription($subscription->id, ['status' => 'cancelled']));
        $this->assertNull(SubscriptionService::deleteSubscription($subscription->id));
    }

    public function test_cancelled_writes_are_reported(): void
    {
        $data = $this->subscriptionData();
        Subscription::creating(fn () => false);

        try {
            SubscriptionService::createSubscription($data);
            $this->fail('A cancelled creation must be reported.');
        } catch (RuntimeException $exception) {
            $this->assertSame('The subscription could not be created.', $exception->getMessage());
        } finally {
            Subscription::flushEventListeners();
        }

        $this->assertDatabaseCount('subscriptions', 0);
        $subscription = SubscriptionService::createSubscription($data);
        Subscription::updating(fn () => false);

        try {
            SubscriptionService::updateSubscription($subscription->id, ['status' => 'cancelled']);
            $this->fail('A cancelled update must be reported.');
        } catch (RuntimeException $exception) {
            $this->assertSame('The subscription could not be updated.', $exception->getMessage());
        } finally {
            Subscription::flushEventListeners();
        }

        Subscription::deleting(fn () => false);

        try {
            SubscriptionService::deleteSubscription($subscription->id);
            $this->fail('A cancelled deletion must be reported.');
        } catch (RuntimeException $exception) {
            $this->assertSame('The subscription could not be deleted.', $exception->getMessage());
        } finally {
            Subscription::flushEventListeners();
        }

        $this->assertDatabaseHas('subscriptions', ['id' => $subscription->id, 'status' => 'pending']);
    }

    /** @return array{provider_id: int, subscription_plan_id: int} */
    private function subscriptionData(): array
    {
        $providerId = DB::table('providers')->insertGetId(['user_id' => User::factory()->create()->id]);

        return ['provider_id' => $providerId, 'subscription_plan_id' => $this->createPlan()];
    }

    private function createPlan(bool $active = true): int
    {
        return DB::table('subscription_plans')->insertGetId([
            'name' => 'Monthly',
            'periodicity' => 'monthly',
            'price' => '100.00',
            'active' => $active,
        ]);
    }
}
