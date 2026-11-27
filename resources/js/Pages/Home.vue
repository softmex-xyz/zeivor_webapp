<script setup lang="ts">
import { router } from "@inertiajs/vue3";
import ExpertBanner from "../Components/HomeComponents/ExpertBanner.vue";
import HowItWorksSection from "../Components/HomeComponents/HowItWorksSection.vue";
import PricingSectionVue from "../Components/HomeComponents/PricingSection.vue";
import AppLayout from "../Layouts/AppLayout.vue";
import DownloadAppBanner from "../Components/DownloadAppBanner.vue";
</script>

<template>
    <AppLayout>
        <div class="min-h-screen bg-white">
            <ExpertBanner @click="router.visit('/experto')" />
            <HowItWorksSection />
            <PricingSectionVue />
            <DownloadAppBanner
                app-store-url="https://apps.apple.com/..."
                google-play-url="https://play.google.com/store/apps/details?id=mx.posibilidades.seivor"
            />
        </div>
    </AppLayout>
</template>
