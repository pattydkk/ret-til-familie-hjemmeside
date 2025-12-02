<?php
/**
 * Default Page Template
 * Bruges når ingen specifik template er valgt
 */

get_header();
$lang = rtf_get_lang();

// Get page content
if (have_posts()) {
    while (have_posts()) {
        the_post();
        ?>
        <div class="page-content" style="max-width: 1200px; margin: 40px auto; padding: 0 20px;">
            <h1 style="font-size: 2.5em; margin-bottom: 30px; color: var(--rtf-text);"><?php the_title(); ?></h1>
            <div style="line-height: 1.8; color: var(--rtf-text);">
                <?php the_content(); ?>
            </div>
        </div>
        <?php
    }
}

get_footer();
