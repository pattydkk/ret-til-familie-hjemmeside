</main>

<?php 
// Show foster care statistics counter ONLY on homepage
if (is_front_page() || is_home()): 
?>
<div id="foster-care-counter" style="background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%); color: white; padding: 2.5rem 1rem; text-align: center; border-top: 4px solid #3b82f6; box-shadow: 0 -4px 6px rgba(0,0,0,0.1);">
    <div style="max-width: 1200px; margin: 0 auto;">
        <h3 style="font-size: 1.4rem; margin-bottom: 1.5rem; font-weight: 600; letter-spacing: 0.5px;">
            📊 Børn fjernet fra hjemmet lige nu / Omhändertagna barn nu
        </h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem; margin-top: 1.5rem;">
            <!-- Denmark Counter -->
            <div style="background: rgba(255,255,255,0.1); border-radius: 12px; padding: 1.5rem; backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.2);">
                <div style="font-size: 0.9rem; opacity: 0.9; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 1px;">🇩🇰 Danmark</div>
                <div id="dk-count" style="font-size: 3rem; font-weight: 700; font-family: 'Arial', sans-serif; margin: 0.5rem 0; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">-</div>
                <div style="font-size: 0.85rem; opacity: 0.85; margin-top: 0.5rem;">
                    <span id="dk-confidence" style="background: rgba(34,197,94,0.2); padding: 0.25rem 0.75rem; border-radius: 20px; border: 1px solid rgba(34,197,94,0.4);">98% præcision</span>
                </div>
                <div id="dk-updated" style="font-size: 0.75rem; opacity: 0.7; margin-top: 0.75rem;">Opdateres...</div>
            </div>
            
            <!-- Sweden Counter -->
            <div style="background: rgba(255,255,255,0.1); border-radius: 12px; padding: 1.5rem; backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.2);">
                <div style="font-size: 0.9rem; opacity: 0.9; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 1px;">🇸🇪 Sverige</div>
                <div id="se-count" style="font-size: 3rem; font-weight: 700; font-family: 'Arial', sans-serif; margin: 0.5rem 0; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">-</div>
                <div style="font-size: 0.85rem; opacity: 0.85; margin-top: 0.5rem;">
                    <span id="se-confidence" style="background: rgba(34,197,94,0.2); padding: 0.25rem 0.75rem; border-radius: 20px; border: 1px solid rgba(34,197,94,0.4);">98% precision</span>
                </div>
                <div id="se-updated" style="font-size: 0.75rem; opacity: 0.7; margin-top: 0.75rem;">Uppdateras...</div>
            </div>
        </div>
        <div style="margin-top: 1.5rem; font-size: 0.8rem; opacity: 0.8; line-height: 1.6;">
            Data baseret på officielle rapporter fra Ankestyrelsen (DK) og Socialstyrelsen (SE)<br>
            Opdateres hver time med realistiske estimater • Data från Ankestyrelsen (DK) och Socialstyrelsen (SE)
        </div>
    </div>
</div>

<script>
(function() {
    let currentDK = 0;
    let currentSE = 0;
    let targetDK = 0;
    let targetSE = 0;
    
    // Animated counter function
    function animateCounter(element, start, end, duration) {
        const startTime = performance.now();
        const difference = end - start;
        
        function update(currentTime) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            // Easing function for smooth animation
            const easeOutQuart = 1 - Math.pow(1 - progress, 4);
            const current = Math.floor(start + difference * easeOutQuart);
            
            element.textContent = current.toLocaleString('da-DK');
            
            if (progress < 1) {
                requestAnimationFrame(update);
            }
        }
        
        requestAnimationFrame(update);
    }
    
    // Format datetime
    function formatDateTime(dateStr) {
        const date = new Date(dateStr);
        const now = new Date();
        const diffMinutes = Math.floor((now - date) / 60000);
        
        if (diffMinutes < 1) return 'Lige nu / Just nu';
        if (diffMinutes < 60) return diffMinutes + ' min siden / för sedan';
        
        const hours = Math.floor(diffMinutes / 60);
        if (hours < 24) return hours + ' time' + (hours !== 1 ? 'r' : '') + ' siden / timmar sedan';
        
        return date.toLocaleString('da-DK', { 
            day: '2-digit', 
            month: '2-digit', 
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
    
    // Fetch and update statistics
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
                    targetDK = data.stats.DK.estimate;
                    if (currentDK === 0) {
                        currentDK = targetDK;
                        dkElement.textContent = targetDK.toLocaleString('da-DK');
                    } else {
                        animateCounter(dkElement, currentDK, targetDK, 2000);
                        currentDK = targetDK;
                    }
                    dkUpdated.textContent = 'Opdateret: ' + formatDateTime(data.stats.DK.updated);
                    dkConfidence.textContent = data.stats.DK.confidence.toFixed(1) + '% præcision';
                }
                
                if (data.stats.SE) {
                    targetSE = data.stats.SE.estimate;
                    if (currentSE === 0) {
                        currentSE = targetSE;
                        seElement.textContent = targetSE.toLocaleString('da-DK');
                    } else {
                        animateCounter(seElement, currentSE, targetSE, 2000);
                        currentSE = targetSE;
                    }
                    seUpdated.textContent = 'Uppdaterad: ' + formatDateTime(data.stats.SE.updated);
                    seConfidence.textContent = data.stats.SE.confidence.toFixed(1) + '% precision';
                }
            }
        } catch (error) {
            console.error('Failed to fetch foster care statistics:', error);
        }
    }
    
    // Initial load
    updateStats();
    
    // Update every 5 minutes (server updates hourly, but we check more frequently)
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
