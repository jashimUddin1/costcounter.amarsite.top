<footer class="bg-dark footer-fixed text-white text-center p-3">
  &copy; <?= date('Y') ?> Developer Jasim. All Rights Reserved.
</footer>

<style>
/* 🔹 Fixed Footer Style */
.footer-fixed {
  position: fixed;
  bottom: 0;
  left: 0;
  width: 100%;
  background-color: #212529;
  color: #f8f9fa;
  text-align: center;
  padding-top: 5px;
  padding-bottom: 15px;
  font-size: 13px;
  z-index: 9999;
  border-top: 1px solid #444;
  opacity: 0;
  transform: translateY(30px);
  animation: footerFadeIn 0.8s ease-out forwards;
}

/* 🔹 Body Padding যাতে Footer ঢেকে না যায় */
body {
  padding-bottom: 60px;
}

/* 🔹 Fade-in Animation */
@keyframes footerFadeIn {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* 🔹 ছোট ডিভাইসে font-size একটু ছোট হবে */
@media (max-width: 576px) {
  .footer-fixed {
    font-size: 12px;
    padding: 8px 0;
  }
}
</style>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= $js_link ?? '' ?>"></script> <!-- optional -->
</body>
</html>
