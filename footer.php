</main>

<?php 
// Show foster care statistics counter ONLY on homepage - COMPACT VERSION
if (is_front_page() || is_home()): 
?>
<div id="foster-care-counter" style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 1.25rem 1rem; text-align: center;">
    <div style="max-width: 900px; margin: 0 auto;">
        <div style="font-size: 0.8rem; color: #64748b; margin-bottom: 0.5rem; font-weight: 500;">
            📊 Børn fjernet fra hjemmet / Omhändertagna barn
        </div>
        <div style="display: flex; justify-content: center; gap: 2rem; flex-wrap: wrap; align-items: center;">
            <!-- Denmark Counter -->
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <span style="font-size: 0.75rem; color: #64748b;">🇩🇰</span>
                <span id="dk-count" style="font-size: 1.25rem; font-weight: 700; color: #1e40af; font-family: 'Arial', sans-serif;">-</span>
                <span id="dk-confidence" style="font-size: 0.7rem; color: #16a34a; background: #f0fdf4; padding: 0.15rem 0.4rem; border-radius: 10px;">98%</span>
            </div>
            
            <!-- Sweden Counter -->
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <span style="font-size: 0.75rem; color: #64748b;">🇸🇪</span>
                <span id="se-count" style="font-size: 1.25rem; font-weight: 700; color: #1e40af; font-family: 'Arial', sans-serif;">-</span>
                <span id="se-confidence" style="font-size: 0.7rem; color: #16a34a; background: #f0fdf4; padding: 0.15rem 0.4rem; border-radius: 10px;">98%</span>
            </div>
        </div>
        <div style="margin-top: 0.5rem; font-size: 0.65rem; color: #94a3b8; line-height: 1.4;">
            <span id="dk-updated"></span> | <span id="se-updated"></span><br>
            Data: <a href="https://ast.dk" target="_blank" style="color: #64748b; text-decoration: none;">Ankestyrelsen</a> (DK) • 
            <a href="https://www.socialstyrelsen.se" target="_blank" style="color: #64748b; text-decoration: none;">Socialstyrelsen</a> (SE)
        </div>
    </div>
</div>

<script>
(function() {
    let currentDK = 0;
    let currentSE = 0;
    
    function animateCounter(element, start, end, duration) {
        const startTime = performance.now();
        const difference = end - start;
        
        function update(currentTime) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const easeOutQuart = 1 - Math.pow(1 - progress, 4);
            const current = Math.floor(start + difference * easeOutQuart);
            
            element.textContent = current.toLocaleString('da-DK');
            
            if (progress < 1) {
                requestAnimationFrame(update);
            }
        }
        
        requestAnimationFrame(update);
    }
    
    function formatDateTime(dateStr) {
        const date = new Date(dateStr);
        const now = new Date();
        const diffMinutes = Math.floor((now - date) / 60000);
        
        if (diffMinutes < 1) return 'nu';
        if (diffMinutes < 60) return diffMinutes + ' min';
        
        const hours = Math.floor(diffMinutes / 60);
        if (hours < 24) return hours + 't';
        
        return date.toLocaleString('da-DK', { 
            day: '2-digit', 
            month: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
    
    async function updateStats() {
        try {
            const response = await fetch('<?php echo rest_url("kate/v1/foster-care-stats"); ?>');
            const data = await response.json();
            
            if (data.success && data.stats) {
                const dkElement = document.getElementById('dk-count');
                const seElement = document.getElementById('se-count');
                const dkUpdated = document.getElementById('dk-updated');
                const seUpdated = document.getElementById('se-updated');
                const dkConfidence = document.getElementById('dk-confidence');
                const seConfidence = document.getElementById('se-confidence');
                
                if (data.stats.DK) {
                    const targetDK = data.stats.DK.estimate;
                    if (currentDK === 0) {
                        currentDK = targetDK;
                        dkElement.textContent = targetDK.toLocaleString('da-DK');
                    } else {
                        animateCounter(dkElement, currentDK, targetDK, 1500);
                        currentDK = targetDK;
                    }
                    dkUpdated.textContent = 'DK: ' + formatDateTime(data.stats.DK.updated);
                    dkConfidence.textContent = data.stats.DK.confidence.toFixed(1) + '%';
                }
                
                if (data.stats.SE) {
                    const targetSE = data.stats.SE.estimate;
                    if (currentSE === 0) {
                        currentSE = targetSE;
                        seElement.textContent = targetSE.toLocaleString('da-DK');
                    } else {
                        animateCounter(seElement, currentSE, targetSE, 1500);
                        currentSE = targetSE;
                    }
                    seUpdated.textContent = 'SE: ' + formatDateTime(data.stats.SE.updated);
                    seConfidence.textContent = data.stats.SE.confidence.toFixed(1) + '%';
                }
            }
        } catch (error) {
            console.error('Failed to fetch foster care statistics:', error);
        }
    }
    
    updateStats();
    setInterval(updateStats, 5 * 60 * 1000);
})();
</script>
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
