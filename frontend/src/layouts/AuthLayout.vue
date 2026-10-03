<template>
  <div class="auth-wrapper">
    <router-view />
  </div>
</template>

<style scoped>
.auth-wrapper {
  width: 100%;
  min-height: 100vh;
  background-color: var(--bg-light);
  transition: background-color 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

html.dark .auth-wrapper {
  background-color: var(--bg-dark);
}
</style>
<script setup lang="ts">

</script>

<style scoped>
.auth-layout {
  background-attachment: fixed;
}
</style>
