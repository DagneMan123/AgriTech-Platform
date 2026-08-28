<template>
  <footer class="footer">
    <div class="max-w-7xl mx-auto px-4">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
        <div>
          <h3 class="text-lg font-bold mb-4">AgriConnect</h3>
          <p class="footer-text text-sm">Smart agriculture platform connecting stakeholders</p>
        </div>
        <div>
          <h4 class="font-bold mb-4">Quick Links</h4>
          <ul class="space-y-2 text-sm">
            <li><a href="#" class="footer-link">Home</a></li>
            <li><a href="#" class="footer-link">About</a></li>
            <li><a href="#" class="footer-link">Contact</a></li>
          </ul>
        </div>
        <div>
          <h4 class="font-bold mb-4">Support</h4>
          <ul class="space-y-2 text-sm">
            <li><a href="#" class="footer-link">Help</a></li>
            <li><a href="#" class="footer-link">Docs</a></li>
            <li><a href="#" class="footer-link">FAQ</a></li>
          </ul>
        </div>
        <div>
          <h4 class="font-bold mb-4">Legal</h4>
          <ul class="space-y-2 text-sm">
            <li><a href="#" class="footer-link">Privacy</a></li>
            <li><a href="#" class="footer-link">Terms</a></li>
            <li><a href="#" class="footer-link">Security</a></li>
          </ul>
        </div>
      </div>
      <div class="footer-divider pt-8 text-center text-sm">
        <p>&copy; 2026 AgriConnect. All rights reserved.</p>
      </div>
    </div>
  </footer>
</template>

<style scoped>
.footer {
  background-color: #1f2937;
  color: #f3f4f6;
  padding: 2rem 0;
  margin-top: 3rem;
  transition: background-color 0.3s ease;
}

html.dark .footer {
  background-color: var(--bg-dark);
}

.footer-text {
  color: #9ca3af;
  transition: color 0.3s ease;
}

html.dark .footer-text {
  color: var(--text-dark-secondary);
}

.footer-link {
  color: #9ca3af;
  transition: color 0.3s ease;
}

.footer-link:hover {
  color: #f3f4f6;
}

html.dark .footer-link {
  color: var(--text-dark-secondary);
}

html.dark .footer-link:hover {
  color: var(--text-dark-primary);
}

.footer-divider {
  border-top: 1px solid #374151;
  color: #9ca3af;
  transition: border-color 0.3s ease, color 0.3s ease;
}

html.dark .footer-divider {
  border-top-color: var(--border-dark);
  color: var(--text-dark-secondary);
}
</style>
