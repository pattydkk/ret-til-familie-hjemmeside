<?php
get_header();
?>
<div class="card">
  <?php if ( have_posts() ) : ?>
    <?php while ( have_posts() ) : the_post(); ?>
      <h1 class="section-title"><?php the_title(); ?></h1>
      <div class="section-lead">
        <?php the_content(); ?>
      </div>
    <?php endwhile; ?>
  <?php else : ?>
    <h1 class="section-title">Indhold ikke fundet</h1>
    <div class="section-lead">
      <p>Der blev ikke fundet indhold på denne adresse.</p>
    </div>
  <?php endif; ?>
</div>
<?php
get_footer();
