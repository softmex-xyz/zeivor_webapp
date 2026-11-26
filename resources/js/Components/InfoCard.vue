<script setup lang="ts">
interface Props {
    title: string;
    description: string;
    background?: string;
    iconColor?: string;
    shape?: "circle" | "diamond" | "triangle";
}

withDefaults(defineProps<Props>(), {
    background: "#ffdade",
    iconColor: "#8b7b7c",
    shape: "circle",
});
</script>

<template>
    <article
        class="w-full rounded-2xl px-6 py-5 sm:px-7 sm:py-6"
        :style="{ backgroundColor: background }"
    >
        <div class="mb-2.5 flex items-center gap-3.5">
            <svg
                class="h-4 w-4 shrink-0 sm:h-[18px] sm:w-[18px]"
                viewBox="0 0 20 20"
                :fill="iconColor"
                aria-hidden="true"
            >
                <circle v-if="shape === 'circle'" cx="10" cy="10" r="8" />
                <polygon
                    v-else-if="shape === 'diamond'"
                    points="10,1 19,10 10,19 1,10"
                />
                <polygon v-else points="10,2 19,18 1,18" />
            </svg>

            <h3
                class="text-lg font-bold leading-snug text-neutral-850 sm:text-lg"
            >
                {{ title }}
            </h3>
        </div>

        <p class="text-sm leading-relaxed text-[#6b6263] sm:text-base">
            {{ description }}
        </p>
    </article>
</template>
