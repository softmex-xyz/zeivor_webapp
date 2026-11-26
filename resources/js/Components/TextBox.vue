<script setup lang="ts">
import { computed, useId } from "vue";

interface Props {
    modelValue?: string;
    label?: string;
    type?: "text" | "email" | "password" | "number" | "tel" | "url" | "search";
    placeholder?: string;
    error?: string;
    hint?: string;
    disabled?: boolean;
    required?: boolean;
    name?: string;
    id?: string;
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: "",
    label: undefined,
    type: "text",
    placeholder: "",
    error: undefined,
    hint: undefined,
    disabled: false,
    required: false,
    name: undefined,
    id: undefined,
});

const emit = defineEmits<{
    (e: "update:modelValue", value: string): void;
    (e: "blur", event: FocusEvent): void;
    (e: "focus", event: FocusEvent): void;
}>();

const generatedId = useId();
const inputId = computed(() => props.id ?? generatedId);

const onInput = (event: Event) => {
    const target = event.target as HTMLInputElement;
    emit("update:modelValue", target.value);
};
</script>

<template>
    <div class="w-full">
        <label
            v-if="label"
            :for="inputId"
            class="mb-1.5 block text-sm font-medium text-neutral-800"
        >
            {{ label }}
            <span v-if="required" class="text-[#ff5a6e]" aria-hidden="true"
                >*</span
            >
        </label>

        <input
            :id="inputId"
            :name="name"
            :type="type"
            :value="modelValue"
            :placeholder="placeholder"
            :disabled="disabled"
            :required="required"
            :aria-invalid="Boolean(error)"
            :aria-describedby="
                error
                    ? `${inputId}-error`
                    : hint
                      ? `${inputId}-hint`
                      : undefined
            "
            @input="onInput"
            @blur="emit('blur', $event)"
            @focus="emit('focus', $event)"
            :class="[
                'block w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-neutral-900 placeholder:text-neutral-400 transition focus:outline-none focus:ring-2',
                error
                    ? 'border-[#ff5a6e] focus:border-[#ff5a6e] focus:ring-[#ff5a6e]/25'
                    : 'border-neutral-300 focus:border-[#ff5a6e] focus:ring-[#ff5a6e]/25',
                disabled
                    ? 'cursor-not-allowed bg-neutral-100 text-neutral-500'
                    : '',
            ]"
        />

        <p
            v-if="error"
            :id="`${inputId}-error`"
            role="alert"
            class="mt-1.5 text-xs font-medium text-[#ff5a6e]"
        >
            {{ error }}
        </p>

        <p
            v-else-if="hint"
            :id="`${inputId}-hint`"
            class="mt-1.5 text-xs text-neutral-500"
        >
            {{ hint }}
        </p>
    </div>
</template>
