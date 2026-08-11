<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h1 class="text-3xl font-bold text-gray-900">My Products</h1>
      <button class="btn-primary">+ Add Product</button>
    </div>
    <div class="card">
      <table class="w-full">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-2 text-left">Product</th>
            <th class="px-4 py-2 text-left">Category</th>
            <th class="px-4 py-2 text-left">Price</th>
            <th class="px-4 py-2 text-left">Stock</th>
            <th class="px-4 py-2 text-left">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr class="border-t">
            <td class="px-4 py-2">Seeds Pack</td>
            <td class="px-4 py-2">Seeds</td>
            <td class="px-4 py-2">Ksh 500</td>
            <td class="px-4 py-2">450</td>
            <td class="px-4 py-2"><button class="text-blue-600">Edit</button></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
