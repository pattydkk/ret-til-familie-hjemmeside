</main>

<?php 
// Show PLATFORM STATISTICS counter ONLY on homepage - REAL-TIME DATA
if (is_front_page() || is_home()): 
    global $wpdb;
    $total_users = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}rtf_platform_users");
    $active_users = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}rtf_platform_users WHERE subscription_status = 'active'");
    $total_posts = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}rtf_platform_posts");
    $total_messages = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}rtf_platform_messages");
?>
<div id="platform-stats-counter" style="background: linear-gradient(135deg, #eef2ff 0%, #f9fafb 100%); border-top: 2px solid #6366f1; padding: 1.5rem 1rem; text-align: center;">
    <div style="max-width: 1000px; margin: 0 auto;">
        <div style="font-size: 0.9rem; color: #475569; margin-bottom: 1rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
            🚀 Ret til Familie Platform - Live Statistik
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; max-width: 900px; margin: 0 auto;">
            <!-- Total Users -->
            <div style="background: white; padding: 1.25rem; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border-left: 4px solid #3b82f6;">
                <div style="font-size: 0.75rem; color: #64748b; margin-bottom: 0.5rem; font-weight: 600; text-transform: uppercase;">👥 Medlemmer i alt</div>
                <div style="font-size: 2rem; font-weight: 700; color: #1e40af; font-family: 'Arial', sans-serif;"><?php echo number_format($total_users); ?></div>
            </div>
            
            <!-- Active Subscriptions -->
            <div style="background: white; padding: 1.25rem; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border-left: 4px solid #10b981;">
                <div style="font-size: 0.75rem; color: #64748b; margin-bottom: 0.5rem; font-weight: 600; text-transform: uppercase;">✅ Aktive abonnementer</div>
                <div style="font-size: 2rem; font-weight: 700; color: #047857; font-family: 'Arial', sans-serif;"><?php echo number_format($active_users); ?></div>
            </div>
            
            <!-- Total Posts -->
            <div style="background: white; padding: 1.25rem; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border-left: 4px solid #f59e0b;">
                <div style="font-size: 0.75rem; color: #64748b; margin-bottom: 0.5rem; font-weight: 600; text-transform: uppercase;">📝 Posts delt</div>
                <div style="font-size: 2rem; font-weight: 700; color: #d97706; font-family: 'Arial', sans-serif;"><?php echo number_format($total_posts); ?></div>
            </div>
            
            <!-- Total Messages -->
            <div style="background: white; padding: 1.25rem; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border-left: 4px solid #8b5cf6;">
                <div style="font-size: 0.75rem; color: #64748b; margin-bottom: 0.5rem; font-weight: 600; text-transform: uppercase;">💬 Beskeder sendt</div>
                <div style="font-size: 2rem; font-weight: 700; color: #7c3aed; font-family: 'Arial', sans-serif;"><?php echo number_format($total_messages); ?></div>
            </div>
        </div>
        <div style="margin-top: 1rem; font-size: 0.7rem; color: #94a3b8;">
            ⚡ Live data opdateres automatisk • Alle tal er real-time fra databasen
        </div>
    </div>
</div>
<?php endif; ?>

<footer class="site-footer">
  <div class="footer-inner">
    <div>
      &copy; <?php echo date('Y'); ?> Ret til Familie / Rätt till Familj / Right to Family
      <div class="social-links">
        <a class="social-btn" href="https://www.facebook.com/profile.php?id=61581408422790" target="_blank" rel="noopener">Facebook (DK)</a>
        <a class="social-btn" href="https://www.facebook.com/profile.php?id=61584459144206" target="_blank" rel="noopener">Facebook (SE)</a>
        <a class="social-btn" href="https://www.youtube.com/@RettilFamilie" target="_blank" rel="noopener">YouTube</a>
        <a class="social-btn" href="https://www.instagram.com/rettilfamilie/" target="_blank" rel="noopener">Instagram</a>
      </div>
    </div>
    <div class="footer-links">
      <a href="mailto:info@rettilfamilie.com">info@rettilfamilie.com</a>
      <a href="mailto:booking@rettilfamilie.com">booking@rettilfamilie.com</a>
      <a href="mailto:bogholderi@rettilfamilie.com">bogholderi@rettilfamilie.com</a>
      <a href="tel:+4530686907">+45 30 68 69 07</a>
      <a href="https://www.iubenda.com/privacy-policy/30362895" class="iubenda-white iubenda-noiframe iubenda-embed" title="Privatlivspolitik">Privatlivspolitik</a>
      <a href="https://www.iubenda.com/privacy-policy/30362895/cookie-policy" class="iubenda-white iubenda-noiframe iubenda-embed" title="Cookiepolitik">Cookiepolitik</a>
    </div>
  </div>
  <script type="text/javascript">
  (function (w,d) {
    var loader = function () {
      var s = d.createElement("script"), tag = d.getElementsByTagName("script")[0];
      s.src="https://cdn.iubenda.com/iubenda.js";
      tag.parentNode.insertBefore(s,tag);
    };
    if(w.addEventListener){w.addEventListener("load", loader, false);}
    else if(w.attachEvent){w.attachEvent("onload", loader);}
    else{w.onload = loader;}
  })(window, document);
  </script>
</footer>
<?php wp_footer(); ?>
</div>
</body>
</html>
