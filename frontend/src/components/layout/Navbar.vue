<template>
  <nav class="bg-white shadow-md">
    <div class="max-w-7xl mx-auto px-4 py-3 flex justify-between items-center">
      <div class="flex items-center">
        <h1 class="text-2xl font-bold text-green-600">AgriConnect</h1>
      </div>
      <div class="flex items-center gap-4">
        <slot name="right" />
      </div>
    </div>
  </nav>
</template>
