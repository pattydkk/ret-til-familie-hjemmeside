<?php
$rtf_lang = function_exists('rtf_get_lang') ? rtf_get_lang() : 'da';
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />

<!-- PWA Meta Tags -->
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="RtF Borger">
<meta name="application-name" content="Ret til Familie">
<meta name="msapplication-TileColor" content="#2563eb">
<meta name="theme-color" content="#2563eb">

<!-- PWA Manifest -->
<link rel="manifest" href="/manifest.json">

<!-- Apple Touch Icons -->
<link rel="apple-touch-icon" sizes="180x180" href="/wp-content/themes/ret-til-familie/assets/icon-192x192.png">
<link rel="icon" type="image/png" sizes="32x32" href="/wp-content/themes/ret-til-familie/assets/icon-96x96.png">
<link rel="icon" type="image/png" sizes="16x16" href="/wp-content/themes/ret-til-familie/assets/icon-72x72.png">
<?php
$slug_for_meta = '';
if (is_front_page()) {
    $slug_for_meta = 'forside';
} else {
    global $post;
    if ($post) {
        $slug_for_meta = $post->post_name;
    }
}
$meta_desc = '';
if ($rtf_lang === 'da') {
    switch ($slug_for_meta) {
        case 'forside':
            $meta_desc = 'Ret til Familie er en tværfaglig socialfaglig virksomhed, der arbejder for retssikkerhed, gennemsigtighed og faglig korrekt håndtering af borgere i Danmark og Sverige.';
            break;
        case 'ydelser':
            $meta_desc = 'Se ydelser og priser hos Ret til Familie: partsrepræsentation, konfliktmægling, sagsgennemgang, psykologiske vurderinger, jobcenterhjælp og handicaprådgivning.';
            break;
        case 'om-os':
            $meta_desc = 'Mød teamet bag Ret til Familie – tværfaglige kræfter fra socialfag, psykologi, jobcenterområdet og handicapområdet i Danmark og Sverige.';
            break;
        case 'kontakt':
            $meta_desc = 'Kontakt Ret til Familie om sager vedrørende børn og familie, jobcenter, handicap, ældre og skilsmisse. Telefon kun til abonnenter og generelle henvendelser.';
            break;
        case 'akademiet':
            $meta_desc = 'Akademiet hos Ret til Familie tilbyder kurser og certifikatforløb om socialret, Barnets Lov, jobcenter og retssikkerhed.';
            break;
        case 'stoet-os':
            $meta_desc = 'Støt Ret til Familie med et fast månedligt beløb via Stripe og bidrag til arbejdet for retssikkerhed og gennemsigtighed i sociale sager.';
            break;
        default:
            $meta_desc = 'Ret til Familie – tværfaglig socialfaglig hjælp til borgere i Danmark og Sverige.';
    }
} elseif ($rtf_lang === 'sv') {
    $meta_desc = 'Ret til Familie/Rätt till Familj ger tvärfackligt stöd i sociala ärenden i Danmark och Sverige med fokus på rättssäkerhet och transparens.';
} else {
    $meta_desc = 'Ret til Familie (Right to Family) provides cross-disciplinary support in social cases in Denmark and Sweden with focus on legal certainty and transparency.';
}
if ($meta_desc) {
    echo '<meta name="description" content="' . esc_attr($meta_desc) . '" />' . "\n";
}
wp_head();
?>
</head>
<body <?php body_class(); ?>>
<div class="site-wrap">
<header class="site-header">
  <div class="header-inner">
    <div class="brand">
      <?php
      if ($rtf_lang === 'sv') {
          echo 'Rätt till Familj';
      } elseif ($rtf_lang === 'en') {
          echo 'Right to Family';
      } else {
          echo 'Ret til Familie';
      }
      ?>
      <span>
        <?php
        if ($rtf_lang === 'sv') {
            echo 'Tvärfaglig social rådgivning i DK & SE';
        } elseif ($rtf_lang === 'en') {
            echo 'Cross-disciplinary social case support';
        } else {
            echo 'Tværfaglig socialfaglig hjælp i DK & SE';
        }
        ?>
      </span>
    </div>
    <nav class="main-nav" id="rtf-main-nav">
      <ul>
        <?php
          global $post;
          $slug = '';
          if (is_front_page()) {
              $slug = 'forside';
          } elseif ($post) {
              $slug = $post->post_name;
          }
          function rtf_link($path, $label, $rtf_lang) {
              $url = home_url('/' . $path . '/');
              if ($path === '' || $path === 'forside') {
                  $url = home_url('/');
              }
              if ($rtf_lang !== 'da') {
                  $join = strpos($url, '?') === false ? '?' : '&';
                  $url .= $join . 'lang=' . $rtf_lang;
              }
              return '<a href="' . esc_url($url) . '">' . esc_html($label) . '</a>';
          }
        ?>
        <li><?php echo rtf_link('forside', ($rtf_lang==='sv' ? 'Startsida' : ($rtf_lang==='en' ? 'Home' : 'Forside')), $rtf_lang); ?></li>
        <li><?php echo rtf_link('om-os', ($rtf_lang==='sv' ? 'Om oss' : ($rtf_lang==='en' ? 'About' : 'Om os')), $rtf_lang); ?></li>
        <li><?php echo rtf_link('ydelser', ($rtf_lang==='sv' ? 'Tjänster' : ($rtf_lang==='en' ? 'Services' : 'Ydelser')), $rtf_lang); ?></li>
        <li><?php echo rtf_link('akademiet', ($rtf_lang==='sv' ? 'Akademiet' : ($rtf_lang==='en' ? 'Academy' : 'Akademiet')), $rtf_lang); ?></li>
        <li><?php echo rtf_link('borger-platform', ($rtf_lang==='sv' ? 'Medborgarplattform' : ($rtf_lang==='en' ? 'Citizen Platform' : 'Borgerplatform')), $rtf_lang); ?></li>
        <li><?php echo rtf_link('kontakt', ($rtf_lang==='sv' ? 'Kontakt' : ($rtf_lang==='en' ? 'Contact' : 'Kontakt')), $rtf_lang); ?></li>
        <li><?php echo rtf_link('stoet-os', ($rtf_lang==='sv' ? 'Stöd oss' : ($rtf_lang==='en' ? 'Support us' : 'Støt os')), $rtf_lang); ?></li>
      </ul>
    </nav>
    <div class="lang-switch">
      <?php
        $base_slug = $slug ? $slug : 'forside';
        $base_url_da = home_url('/' . ($base_slug === 'forside' ? '' : $base_slug . '/'));
        $base_url_sv = $base_url_da . (strpos($base_url_da, '?') === false ? '?lang=sv' : '&lang=sv');
        $base_url_en = $base_url_da . (strpos($base_url_da, '?') === false ? '?lang=en' : '&lang=en');
        $base_url_da = $base_url_da; // default DA uden lang-parameter
      ?>
      <a href="<?php echo esc_url($base_url_da); ?>" class="<?php echo $rtf_lang==='da' ? 'active' : ''; ?>">DA</a>
      <a href="<?php echo esc_url($base_url_sv); ?>" class="<?php echo $rtf_lang==='sv' ? 'active' : ''; ?>">SV</a>
      <a href="<?php echo esc_url($base_url_en); ?>" class="<?php echo $rtf_lang==='en' ? 'active' : ''; ?>">EN</a>
    </div>
  </div>
</header>

<!-- PWA Initialization -->
<script src="/pwa-init.js" defer></script>

<main class="site-main">