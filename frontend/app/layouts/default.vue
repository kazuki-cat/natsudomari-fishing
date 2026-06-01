<script setup lang="ts">
useHead({
  titleTemplate: "%s | 夏泊釣り情報",
});
</script>
<template>
  <div class="min-h-screen flex flex-col">
    <TheHeader />
    <main class="flex-1">
      <slot />
    </main>
    <TheFooter />
  </div>
</template>
