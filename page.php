<?php
get_header();
$rtf_lang = function_exists('rtf_get_lang') ? rtf_get_lang() : 'da';
global $post;
$slug = '';
if (is_front_page()) {
    $slug = 'forside';
} elseif ($post) {
    $slug = $post->post_name;
}
?>
<div class="card">
<?php
switch ($slug) :
  case 'forside':
    if ($rtf_lang === 'sv') : ?>
  <section class="hero">
    <div>
      <h1 class="hero-title">Rätt till Familj – tvärfackligt stöd i sociala ärenden</h1>
      <p class="hero-subtitle">
        Ret til Familie/Rätt till Familj är en tvärfacklig social verksamhet som arbetar för rättssäkerhet,
        transparens och fackligt korrekt hantering av medborgare i Danmark och Sverige. Vi är oberoende av kommunerna
        och står på medborgarens sida.
      </p>
      <div class="hero-badges">
        <span class="badge">Barnlag/Barnets Lov och placeringsärenden</span>
        <span class="badge">Jobcenter / Arbetsförmedlingen</span>
        <span class="badge">Funktionsnedsättning och specialområdet</span>
        <span class="badge">Konflikthantering och partsrepresentation</span>
      </div>
      <div class="hero-cta">
        <a class="btn-primary" href="BOOKING_LINK_HER">
          Boka tid (bokningslänk läggs till)
        </a>
        <a class="btn-secondary" href="<?php echo esc_url( home_url('/ydelser/?lang=sv') ); ?>">
          Se tjänster och priser
        </a>
        <a class="support-btn" href="https://buy.stripe.com/7sY8wQ4NA2i94Vn6z493y0b" target="_blank" rel="noopener">
          Stöd vårt arbete (20–100 DKK/mån)
        </a>
      </div>
    </div>
    <aside class="hero-side">
      <div>
        <h2>Om Rätt till Familj</h2>
        <p>
          Vi samlar socialfackliga konsulenter, psykologer, jobcenter-specialister och funktionshinderkompetenser
          på en gemensam plattform. Vi arbetar tvärfackligt, dokumentationsbaserat och med fokus på den enskildes
          rättssäkerhet och insyn i sitt ärende.
        </p>

        <p>
          Vi kan också bokas för föredrag och konferenser om det sociala området, rättssäkerhet och samarbetet mellan medborgare och myndigheter.
        </p>
        <p style="margin-top:6px; font-weight:600;">
          Du är också välkommen att boka ett förutsättningslöst samtal för att se om vi och Rätt till Familj är rätt match för ditt ärende.
        </p>
      </div>
      <div>
        <h2>Hur vi arbetar</h2>
        <p>
          Vi hjälper med partsrepresentation, ärendeanalys, stöd vid möten, överklaganden och struktur i processer
          som ofta känns oöverskådliga. Målet är att göra systemet mer genomskinligt – och mindre övermäktigt.
        </p>
      </div>
      <div class="hero-tags">
        <span class="hero-tag">Rättssäkerhet</span>
        <span class="hero-tag">Transparens</span>
        <span class="hero-tag">Tvärfacklig rådgivning</span>
        <span class="hero-tag">Danmark</span>
        <span class="hero-tag">Sverige</span>
      </div>
    </aside>
  </section>
<?php elseif ($rtf_lang === 'en') : ?>
  <section class="hero">
    <div>
      <h1 class="hero-title">Right to Family – cross-disciplinary support in social cases</h1>
      <p class="hero-subtitle">
        Ret til Familie / Right to Family is a cross-disciplinary social practice working for legal certainty,
        transparency and professionally correct handling of citizens in Denmark and Sweden. We are independent of
        the municipalities and stand on the citizen’s side.
      </p>
      <div class="hero-badges">
        <span class="badge">Child protection & placements</span>
        <span class="badge">Job centre & employment cases</span>
        <span class="badge">Disability & special area</span>
        <span class="badge">Conflict mediation & representation</span>
      </div>
      <div class="hero-cta">
        <a class="btn-primary" href="BOOKING_LINK_HER">
          Book a meeting (link coming)
        </a>
        <a class="btn-secondary" href="<?php echo esc_url( home_url('/ydelser/?lang=en') ); ?>">
          View services and prices
        </a>
        <a class="support-btn" href="https://buy.stripe.com/7sY8wQ4NA2i94Vn6z493y0b" target="_blank" rel="noopener">
          Support our work (20–100 DKK/month)
        </a>
      </div>
    </div>
    <aside class="hero-side">
      <div>
        <h2>About Right to Family</h2>
        <p>
          We bring together social work consultants, psychologists, job centre specialists and disability expertise
          on one platform. We work across disciplines and focus on documentation, law and procedure to protect
          the citizen’s rights in complex systems.
        </p>

        <p>
          We can also be booked for lectures and conferences related to social affairs, legal certainty and the cooperation between citizens and authorities.
        </p>
        <p style="margin-top:6px; font-weight:600;">
          You are also welcome to schedule a non-binding call to determine whether we and Right to Family are the right match for your situation.
        </p>
      </div>
      <div>
        <h2>How we work</h2>
        <p>
          We assist with representation, case analysis, meeting support, complaints and strategy – from first concern
          to follow-up. The goal is to reduce uncertainty and give citizens a clearer, stronger position.
        </p>
      </div>
      <div class="hero-tags">
        <span class="hero-tag">Legal certainty</span>
        <span class="hero-tag">Transparency</span>
        <span class="hero-tag">Cross-disciplinary</span>
        <span class="hero-tag">Denmark</span>
        <span class="hero-tag">Sweden</span>
      </div>
    </aside>
  </section>
<?php else : ?>
  <section class="hero">
    <div>
      <h1 class="hero-title">Ret til Familie – tværfaglig hjælp i sociale sager</h1>
      <p class="hero-subtitle">
        Ret til Familie er en tværfaglig socialfaglig virksomhed, der arbejder for retssikkerhed, gennemsigtighed
        og faglig korrekt håndtering af borgere i Danmark og Sverige. Vi er uafhængige af kommunerne og står på
        borgernes side i mødet med systemet.
      </p>
      <div class="hero-badges">
        <span class="badge">Barnets Lov & anbringelser</span>
        <span class="badge">Jobcenter- og beskæftigelse</span>
        <span class="badge">Handicap- og specialområdet</span>
        <span class="badge">Konflikthåndtering & partsrepræsentation</span>
      </div>
      <div class="hero-cta">
        <a class="btn-primary" href="BOOKING_LINK_HER">
          Book en tid (bookinglink kommer)
        </a>
        <a class="btn-secondary" href="<?php echo esc_url( home_url('/ydelser/') ); ?>">
          Se ydelser og priser
        </a>
        <a class="support-btn" href="https://buy.stripe.com/7sY8wQ4NA2i94Vn6z493y0b" target="_blank" rel="noopener">
          Støt vores arbejde (20–100 kr./md)
        </a>
      </div>
    </div>
    <aside class="hero-side">
      <div>
        <h2>Om Ret til Familie</h2>
        <p>
          Vi samler socialfaglige konsulenter, psykologer, jobcenter-specialister og handicapfaglige kompetencer i én
          samlet platform. Vi arbejder dokumentationsbaseret, lovstyret og med fokus på borgerens retssikkerhed i komplekse sager.
        </p>
      </div>
      <div>
        <h2>Hvordan vi arbejder</h2>
        <p>
          Vi hjælper med partsrepræsentation, sagsanalyse, deltagelse i møder, klager og strategi – fra første bekymring
          til opfølgning. Målet er, at borgeren får indsigt, overblik og et stærkere ståsted.
        </p>
        <p style="margin-top:6px;">
          Vi kan også bookes til foredrag og konferencer om socialområdet, retssikkerhed og samarbejdet mellem borgere og myndigheder.
        </p>

        <p style="margin-top:6px; font-weight:600;">
          Du er også velkommen til et uforpligtende opkald for at høre, om vi og Ret til Familie er det rette match til din sag.
        </p>
      </div>
      <div class="hero-tags">
        <span class="hero-tag">Retssikkerhed</span>
        <span class="hero-tag">Gennemsigtighed</span>
        <span class="hero-tag">Tværfaglighed</span>
        <span class="hero-tag">Danmark</span>
        <span class="hero-tag">Sverige</span>
      </div>
    </aside>
  </section>
<?php
    endif;
    break;

  

case 'ydelser':
    if ($rtf_lang === 'sv') : ?>
  <h1 class="section-title">Tjänster – vad vi kan hjälpa med</h1>
  <p class="section-lead">
    Här får du en tydlig överblick över vad vi arbetar med i Danmark och Sverige. Vi kombinerar dokumentationsgenomgång,
    partsrepresentation, stöd vid möten och löpande rådgivning. Pris och upplägg avtalas utifrån ärendets omfattning.
  </p>
  <div class="grid-2">
    <div>
      <h2>Rådgivning & löpande stöd</h2>
      <div class="info-box">
        <p>
          Vi erbjuder socialfacklig rådgivning till föräldrar, anhöriga och enskilda medborgare. Fokus är att skapa
          struktur i ärendet, förklara nästa steg och få dokumentation och kommunikation på plats.
        </p>
        <p style="margin-top:6px;">
          Vi arbetar bland annat med barn- och familjeärenden, jobcenter/Arbetsförmedlingen, funktionshinderområdet
          och frågor som rör äldreomsorg.
        </p>
      </div>

      <h2 style="margin-top:24px;">Genomgång av ärenden</h2>
      <div class="info-box">
        <p>
          Vi går systematiskt igenom akter, beslut, journalnotater och bilagor. Vi identifierar brister, oklarheter
          och möjliga spår för dialog eller klagomål och samlar det i en skriftlig, lättöverskådlig sammanfattning.
        </p>
      </div>

      <h2 style="margin-top:24px;">Konflikthantering</h2>
      <div class="info-box">
        <p>
          När samarbetet med myndigheter, skola eller andra aktörer har kört fast kan vi hjälpa med struktur i dialogen,
          förberedelse till möten och med att få fokus tillbaka på sakfrågan och barnets bästa.
        </p>
      </div>
    </div>
    <div>
      <h2>Jobcenter / Arbetsförmedlingen</h2>
      <div class="info-box">
        <p>
          Stöd i kontakt med Jobcenter eller Arbetsförmedlingen – till exempel vid sjukskrivning, resursförlopp,
          praktik, rehabilitering eller hot om sanktioner. Vi hjälper med att läsa besluten, formulera svar
          och följa med som stödperson på möten.
        </p>
      </div>

      <h2 style="margin-top:24px;">Funktionsnedsättning, autism & ADHD</h2>
      <div class="info-box">
        <p>
          Rådgivning kring rättigheter, stöd, hjälpmedel och särskilda insatser för både barn och vuxna med
          funktionsnedsättning, autism eller ADHD. Vi hjälper med att förbereda ansökningar och strukturera underlag.
        </p>
      </div>

      <h2 style="margin-top:24px;">Abonnemang & plattform</h2>
      <div class="info-box">
        <p>
          Med vårt abonnemang får du löpande tillgång till grundläggande rådgivning och vår kunskapsplattform
          med guider och material. Abonnemanget löper månadsvis tills det sägs upp via e-post.
        </p>
        <p style="margin-top:10px;">
          <a class="btn-primary" href="https://buy.stripe.com/eVq4gAdk6aOFcnP0aG93y08" target="_blank" rel="noopener">
            Teckna abonnemang (49 DKK / mån)
          </a>
        </p>
      </div>

      <div class="info-box" style="margin-top:24px;">
        <p>
          Vill du veta mer om hur vi kan hjälpa i just ditt ärende, skriv till <strong>booking@rettilfamilie.com</strong>
          så återkommer vi med förslag till upplägg och pris.
        </p>
      </div>
    </div>
  </div>
<?php elseif ($rtf_lang === 'en') : ?>
  <h1 class="section-title">Services – how we can help</h1>
  <p class="section-lead">
    This page gives you a clear overview of what we work with in Denmark and Sweden. We combine case review, representation,
    meeting support and ongoing advice. Pricing and setup are agreed based on the scope and complexity of your case.
  </p>
  <div class="grid-2">
    <div>
      <h2>Guidance & ongoing support</h2>
      <div class="info-box">
        <p>
          We provide social work–oriented guidance to parents, relatives and individual citizens. The focus is to create
          structure in the case, clarify next steps and get documentation and communication under control.
        </p>
        <p style="margin-top:6px;">
          We work with, among other things, children and family matters, job centre cases, disability issues
          and questions related to elderly care.
        </p>
      </div>

      <h2 style="margin-top:24px;">Case review</h2>
      <div class="info-box">
        <p>
          We systematically review files, decisions, case notes and attachments. We identify gaps, inconsistencies and
          possible avenues for dialogue or complaint and summarise this in a written, easy-to-understand overview.
        </p>
      </div>

      <h2 style="margin-top:24px;">Conflict resolution</h2>
      <div class="info-box">
        <p>
          When cooperation with authorities, schools or other actors has broken down, we help structure the dialogue,
          prepare meetings and bring the focus back to the core issues and the best interests of the child.
        </p>
      </div>
    </div>
    <div>
      <h2>Job centre support</h2>
      <div class="info-box">
        <p>
          Support in dealing with the job centre – for example in relation to sickness benefit, trajectories,
          work trials, rehabilitation or threats of sanctions. We help you understand decisions, formulate responses
          and attend meetings as a support person.
        </p>
      </div>

      <h2 style="margin-top:24px;">Disability, autism & ADHD</h2>
      <div class="info-box">
        <p>
          Advice on rights, support, assistive devices and special measures for children and adults with disabilities,
          autism or ADHD. We help prepare applications and structure the necessary documentation.
        </p>
      </div>

      <h2 style="margin-top:24px;">Subscription & platform</h2>
      <div class="info-box">
        <p>
          With our subscription you get ongoing access to basic guidance and our knowledge platform with guides and materials.
          The subscription continues month by month until you cancel by e-mail.
        </p>
        <p style="margin-top:10px;">
          <a class="btn-primary" href="https://buy.stripe.com/eVq4gAdk6aOFcnP0aG93y08" target="_blank" rel="noopener">
            Start subscription (49 DKK / month)
          </a>
        </p>
      </div>

      <div class="info-box" style="margin-top:24px;">
        <p>
          To find out how we can support you in your specific case, write to <strong>booking@rettilfamilie.com</strong>
          and we will return with a proposed plan and price frame.
        </p>
      </div>
    </div>
  </div>
<?php else : ?>
  <h1 class="section-title">Ydelser – hvad vi kan hjælpe med</h1>
  <p class="section-lead">
    Her får du et samlet overblik over, hvad vi arbejder med i Danmark og Sverige. Vi kombinerer sagsgennemgang,
    partsrepræsentation, mødestøtte og løbende rådgivning. Pris og forløb aftales ud fra sagens omfang og kompleksitet.
  </p>
  <div class="grid-2">
    <div>
      <h2>Rådgivning & løbende sparring</h2>
      <div class="info-box">
        <p>
          Vi tilbyder socialfaglig rådgivning til forældre, pårørende og borgere, der står midt i en sag. Fokus er at skabe
          struktur, afklare næste skridt og få styr på dokumentation og skriftlig kommunikation.
        </p>
        <p style="margin-top:6px;">
          Vi arbejder bl.a. med børn- og familiesager, jobcenterforløb, handicapområdet og ældreområdet.
        </p>
      </div>

      <h2 style="margin-top:24px;">Sagsgennemgang</h2>
      <div class="info-box">
        <p>
          Vi gennemgår akter, afgørelser, journalnotater og bilag systematisk. Vi samler fejl, mangler og mulige veje videre
          i en skriftlig, overskuelig opsamling, der kan bruges i dialogen med myndigheder eller som grundlag for klage.
        </p>
      </div>

      <h2 style="margin-top:24px;">Konflikthåndtering</h2>
      <div class="info-box">
        <p>
          Når samarbejdet med kommune, skole eller andre aktører er kørt fast, hjælper vi med at skabe retning i dialogen,
          forberede møder og få fokus tilbage på sagen og barnets bedste.
        </p>
      </div>
    </div>
    <div>
      <h2>Jobcenterområdet</h2>
      <div class="info-box">
        <p>
          Hjælp i sager om sygedagpenge, ressourceforløb, praktik, rehabilitering og trussel om sanktioner. Vi hjælper med
          at gennemgå afgørelser, formulere svar og deltager som bisidder på møder, når der er behov for det.
        </p>
      </div>

      <h2 style="margin-top:24px;">Handicap, autisme & ADHD</h2>
      <div class="info-box">
        <p>
          Rådgivning om rettigheder, støtte, hjælpemidler og særlige indsatser til både børn og voksne med handicap,
          autisme eller ADHD. Vi hjælper med at forberede ansøgninger og strukturere det nødvendige materiale.
        </p>
      </div>

      <h2 style="margin-top:24px;">Abonnement & vidensplatform</h2>
      <div class="info-box">
        <p>
          Med vores abonnement får du løbende adgang til grundlæggende rådgivning og til vores vidensplatform med guides
          og materiale. Abonnementet fortsætter måned for måned, indtil du opsiger det skriftligt via e-mail.
        </p>
        <p style="margin-top:10px;">
          <a class="btn-primary" href="https://buy.stripe.com/eVq4gAdk6aOFcnP0aG93y08" target="_blank" rel="noopener">
            Tegn abonnement (49 kr./md)
          </a>
        </p>
      </div>

      <div class="info-box" style="margin-top:24px;">
        <p>
          Vil du høre mere om, hvordan vi kan hjælpe i netop din sag, så skriv til <strong>booking@rettilfamilie.com</strong>.
          Så vender vi tilbage med et forslag til forløb og prisramme.
        </p>
      </div>
    </div>
  </div>
<?php
    endif;
    break;

case 'om-os':
    if ($rtf_lang === 'sv') : ?>
  <h1 class="section-title">Om Rätt till Familj</h1>
  <p class="section-lead">
    Rätt till Familj är en oberoende, tvärfacklig verksamhet som hjälper medborgare att få överblick, rättssäkerhet
    och mer lugn i ärenden som annars kan kännas övermäktiga. Vi arbetar inom barn- och familjeområdet, jobcenter/
    Arbetsförmedlingen, funktionshinder och äldreområdet – alltid med fokus på medborgarens rättigheter.
  </p>
  <div class="grid-2">
    <div>
      <h2>Vår inriktning</h2>
      <div class="info-box">
        <p>
          Vi kombinerar socialfacklig kunskap, systemkännedom och erfarenheter från människor som själva har varit i processen.
          Vi hjälper till att förstå akter och beslut, planera nästa steg och hålla kommunikationen med myndigheter tydlig
          och samlad.
        </p>
      </div>
      <div class="info-box" style="margin-top:14px;">
        <h3>Oberoende stöd</h3>
        <p>
          Vi är inte en del av kommunen och arbetar uteslutande på medborgarens sida. Vi ställer de kritiska frågorna,
          samlar tråden i ärendet och är ett fast stöd före, under och efter möten.
        </p>
      </div>
    </div>
    <div>
      <h2>Team och kompetenser</h2>
      <div class="info-box">
        <p>
          Rätt till Familj samlar socialfackliga konsulenter och specialiserade samarbetspartner på tvärs av socialområdet.
          Vid behov samarbetar vi med externa jurister och psykologer för att säkra ett starkt, tvärfackligt stöd.
        </p>
        <p style="margin-top:6px;">
          Vi bygger löpande ut vårt nätverk, så fler medborgare kan få kvalificerat stöd på ett och samma ställe.
        </p>
      </div>
    </div>
  </div>
<?php elseif ($rtf_lang === 'en') : ?>
  <h1 class="section-title">About Right to Family</h1>
  <p class="section-lead">
    Right to Family is an independent, cross-disciplinary practice that helps citizens gain overview, legal certainty
    and more clarity in complex social cases. We work within children and family matters, job centre cases, disability
    issues and elderly care – always with a focus on the citizen&apos;s rights.
  </p>
  <div class="grid-2">
    <div>
      <h2>Our approach</h2>
      <div class="info-box">
        <p>
          We combine social work expertise, system knowledge and lived experience from people who have been through
          the system themselves. We help you understand your documents and decisions, plan realistic next steps and keep
          communication with the authorities structured and clear.
        </p>
      </div>
      <div class="info-box" style="margin-top:14px;">
        <h3>Independent support</h3>
        <p>
          We are not part of the municipality and work solely on the citizen&apos;s side. This allows us to ask the critical
          questions, connect the dots and be a consistent partner before, during and after meetings.
        </p>
      </div>
    </div>
    <div>
      <h2>Team and expertise</h2>
      <div class="info-box">
        <p>
          Right to Family brings together social work consultants and specialised partners across the social sector.
          When needed, we collaborate with external lawyers and psychologists to ensure strong, cross-disciplinary support.
        </p>
        <p style="margin-top:6px;">
          Our network is continuously expanding so more citizens can access qualified help in one place.
        </p>
      </div>
    </div>
  </div>
<?php else : ?>
  <h1 class="section-title">Om Ret til Familie</h1>
  <p class="section-lead">
    Ret til Familie er en uafhængig, tværfaglig virksomhed, der hjælper borgere med at skabe overblik, retssikkerhed
    og ro i komplekse sager mod kommunen. Vi arbejder på tværs af børn- og familieområdet, jobcenter, handicap og
    ældreområdet – altid med fokus på borgerens rettigheder.
  </p>
  <div class="grid-2">
    <div>
      <h2>Vores tilgang</h2>
      <div class="info-box">
        <p>
          Vi kombinerer socialfaglig viden, systemkendskab og erfaringer fra borgere, der selv har stået i systemet.
          Vi hjælper med at forstå akter og afgørelser, planlægge næste skridt og samle kommunikationen med myndighederne
          i et klart og overskueligt forløb.
        </p>
      </div>
      <div class="info-box" style="margin-top:14px;">
        <h3>Uafhængig rådgivning</h3>
        <p>
          Vi er ikke en del af kommunen og arbejder udelukkende på borgerens side. Vi stiller de kritiske spørgsmål,
          samler trådene i sagen og er en fast medspiller før, under og efter møder.
        </p>
      </div>
    </div>
    <div>
      <h2>Team & kompetencer</h2>
      <div class="info-box">
        <p>
          Ret til Familie samler socialfaglige konsulenter og specialiserede samarbejdspartnere på tværs af socialområdet.
          Ved behov samarbejder vi med eksterne jurister og psykologer, så borgere kan få stærk, tværfaglig støtte ét sted.
        </p>
        <p style="margin-top:6px;">
          Vores netværk udbygges løbende, så flere borgere kan få kvalificeret hjælp til deres sag.
        </p>
      </div>
    </div>
  </div>
<?php
    endif;
    break;
case 'akademiet':
    if ($rtf_lang === 'sv') : ?>
  <h1 class="section-title">Akademiet – kurser och certifikatförlopp</h1>
  <p class="section-lead">
    Akademiet samlar kurser och förlopp som ger praktisk kunskap om sociala ärenden. Innehållet kan användas av
    medborgare, anhöriga och fackpersoner som vill stå starkare i mötet med systemet i både Danmark och Sverige.
  </p>
  <div class="grid-2">
    <div>
      <h2>Kursområden</h2>
      <ul class="list">
        <li>Grundförståelse av ärenden enligt Barnlag/Barnets Lov</li>
        <li>Rättssäkerhet och klagomöjligheter i sociala ärenden</li>
        <li>Jobcenter- och sjukpenningärenden</li>
        <li>Konflikthantering mellan medborgare och system</li>
        <li>Dokumentation, aktinsyn och felidentifiering</li>
        <li>Samarbete med anhöriga och nätverk</li>
      </ul>
      <p style="margin-top:12px; font-size:0.9rem; color:#9ca3af;">
        Kurserna byggs som moduler, så att du kan ta dem enskilt eller som längre förlopp beroende på behov och nivå.
      </p>
    </div>
    <div>
      <h2>Certifikat och avgångsbevis</h2>
      <div class="info-box">
        <h3>Examen och dokumentation</h3>
        <p>
          Varje kurs avslutas med test eller praktisk uppgift. Godkänt förlopp ger ett digitalt avgångsbevis
          med kursnamn, längd och innehåll som kan användas gentemot arbetsgivare och samarbetspartner.
        </p>
      </div>
      <div class="info-box" style="margin-top:14px;">
        <h3>Målgrupper</h3>
        <p>
          Akademiet är relevant för medborgare, anhöriga, partsrepresentanter, socialsekreterare, jurister, psykologer
          och andra fackpersoner som arbetar med sociala ärenden i praktiken.
        </p>
      </div>
    </div>
  </div>
<?php elseif ($rtf_lang === 'en') : ?>
  <h1 class="section-title">The Academy – courses and certificate programmes</h1>
  <p class="section-lead">
    The Academy gathers courses and learning tracks that provide practical knowledge about social cases. Content can be
    used by citizens, relatives and professionals who want to stand stronger when dealing with the system in Denmark and Sweden.
  </p>
  <div class="grid-2">
    <div>
      <h2>Course areas</h2>
      <ul class="list">
        <li>Basic understanding of child protection and Barnets Lov</li>
        <li>Legal certainty and complaint options in social cases</li>
        <li>Job centre and sickness benefit cases</li>
        <li>Conflict handling between citizen and system</li>
        <li>Documentation, access to files and error detection</li>
        <li>Collaboration with relatives and networks</li>
      </ul>
      <p style="margin-top:12px; font-size:0.9rem; color:#9ca3af;">
        Courses are built as modules so you can take them individually or as longer programmes depending on needs and level.
      </p>
    </div>
    <div>
      <h2>Certificates and documentation</h2>
      <div class="info-box">
        <h3>Exam and proof</h3>
        <p>
          Each course ends with a test or practical assignment. Passing the course gives a digital certificate
          with course name, duration and content that can be used with employers and partners.
        </p>
      </div>
      <div class="info-box" style="margin-top:14px;">
        <h3>Target groups</h3>
        <p>
          The Academy is relevant for citizens, relatives, representatives, social workers, lawyers, psychologists
          and other professionals working with social cases in practice.
        </p>
      </div>
    </div>
  </div>
<?php else : ?>
  <h1 class="section-title">Akademiet – kurser og certifikatforløb</h1>
  <p class="section-lead">
    Akademiet samler kurser og forløb, der giver praksisnær viden om sociale sager. Indholdet kan bruges af både borgere,
    pårørende og fagpersoner, der vil stå stærkere i mødet med systemet i Danmark og Sverige.
  </p>
  <div class="grid-2">
    <div>
      <h2>Kursusområder</h2>
      <ul class="list">
        <li>Grundforståelse af sager efter Barnets Lov</li>
        <li>Retssikkerhed og klagemuligheder i sociale sager</li>
        <li>Jobcenter- og sygedagpengesager</li>
        <li>Konflikthåndtering mellem borger og system</li>
        <li>Dokumentation, aktindsigt og fejlfinding</li>
        <li>Samarbejde med pårørende og netværk</li>
      </ul>
      <p style="margin-top:12px; font-size:0.9rem; color:#9ca3af;">
        Kurserne bygges som moduler, så du kan tage dem enkeltvis eller som længere forløb afhængigt af behov og niveau.
      </p>
    </div>
    <div>
      <h2>Certifikat og afgangsbevis</h2>
      <div class="info-box">
        <h3>Eksamen og dokumentation</h3>
        <p>
          Hvert kursus afsluttes med test eller praktisk opgave. Bestået forløb udløser et digitalt afgangsbevis
          med kursusnavn, varighed og indhold, der kan bruges over for arbejdsgivere og samarbejdspartnere.
        </p>
      </div>
      <div class="info-box" style="margin-top:14px;">
        <h3>Målgrupper</h3>
        <p>
          Akademiet er relevant for borgere, pårørende, partsrepræsentanter, socialrådgivere, jurister, psykologer
          og andre fagpersoner, der arbejder med sociale sager i praksis.
        </p>
      </div>
    </div>
  </div>
<?php
    endif;
    break;

  
case 'kontakt':
    if ($rtf_lang === 'sv') : ?>
  <h1 class="section-title">Kontakt Rätt till Familj</h1>
  <p class="section-lead">
    Du är välkommen att kontakta oss om du har frågor om din sak, vill höra mer om vad vi kan hjälpa med,
    eller vill boka en tid när bokningslänken är på plats.
  </p>
  <div class="grid-2">
    <div>
      <h2>Kontaktvägar</h2>
      <div class="info-box">
        <p><strong>Telefon:</strong> 30 68 69 07 (för abonnenter och allmänna frågor – inte för bokning)</p>
        <p><strong>E-post – allmän info:</strong> info@rettilfamilie.com</p>
        <p><strong>E-post – bokning:</strong> booking@rettilfamilie.com</p>
        <p><strong>E-post – ekonomi/abonnemang:</strong> bogholderi@rettilfamilie.com</p>
      </div>
    </div>
    <div>
      <h2>Bokning & betalning</h2>
      <div class="info-box">
        <p>
          Alla faktiska bokningar sker via vår bokningslänk (när den är klar). Betalning sker via Stripe för säker
          hantering av betalningar. Tills bokningslänken är aktiv kan du skriva till booking@rettilfamilie.com.
        </p>
      </div>
    </div>
  </div>
<?php elseif ($rtf_lang === 'en') : ?>
  <h1 class="section-title">Contact Right to Family</h1>
  <p class="section-lead">
    You are welcome to contact us if you have questions about your case, want to know more about how we work,
    or wish to book a meeting once the booking link is ready.
  </p>
  <div class="grid-2">
    <div>
      <h2>Contact details</h2>
      <div class="info-box">
        <p><strong>Phone:</strong> 30 68 69 07 (for subscribers and general questions – not for booking)</p>
        <p><strong>E-mail – general info:</strong> info@rettilfamilie.com</p>
        <p><strong>E-mail – booking:</strong> booking@rettilfamilie.com</p>
        <p><strong>E-mail – accounting/subscriptions:</strong> bogholderi@rettilfamilie.com</p>
      </div>
    </div>
    <div>
      <h2>Booking & payment</h2>
      <div class="info-box">
        <p>
          All bookings are made via our booking link (once available). Payment is handled via Stripe for secure processing.
          Until the booking link is active, you can write to booking@rettilfamilie.com to arrange a meeting.
        </p>
      </div>
    </div>
  </div>
<?php else : ?>
  <h1 class="section-title">Kontakt Ret til Familie</h1>
  <p class="section-lead">
    Du er velkommen til at kontakte os, hvis du har spørgsmål til din sag, vil høre mere om vores ydelser,
    eller ønsker at aftale et forløb, når bookinglinket er på plads.
  </p>
  <div class="grid-2">
    <div>
      <h2>Kontaktoplysninger</h2>
      <div class="info-box">
        <p><strong>Telefon:</strong> 30 68 69 07 (til abonnenter og generelle henvendelser – ikke booking)</p>
        <p><strong>E-mail – generel info:</strong> info@rettilfamilie.com</p>
        <p><strong>E-mail – booking:</strong> booking@rettilfamilie.com</p>
        <p><strong>E-mail – bogholderi/abonnement:</strong> bogholderi@rettilfamilie.com</p>
      </div>
    </div>
    <div>
      <h2>Booking & betaling</h2>
      <div class="info-box">
        <p>
          Selve booking foregår via vores bookinglink (når det er klar). Al betaling håndteres via Stripe for sikker
          betaling. Indtil bookinglinket er aktivt, kan du skrive til booking@rettilfamilie.com for at aftale en tid.
        </p>
      </div>
    </div>
  </div>
<?php
    endif;
    break;
case 'stoet-os':
    if ($rtf_lang === 'sv') : ?>
  <h1 class="section-title">Stöd Rätt till Familj</h1>
  <p class="section-lead">
    Om du vill stödja arbetet med rättssäkerhet, transparens och fackligt korrekt hantering av sociala ärenden kan du
    ge ett fast månadsbidrag. Alla betalningar hanteras via Stripe för säker betalning. Belopp anges i DKK (danska kronor).
  </p>
  <div class="info-box">
    <h3>Fast månadsstöd</h3>
    <p>
      Via knappen nedan kan du stödja Ret til Familie i vårt arbete med analyser och förbättring av området, med ett belopp
      mellan ca 20 och 100 DKK per månad, tills du säger upp det via e-post till info@rettilfamilie.com.
    </p>
    <p style="margin-top:10px;">
      <a class="support-btn" href="https://buy.stripe.com/7sY8wQ4NA2i94Vn6z493y0b" target="_blank" rel="noopener">
        Stöd vårt arbete (20–100 DKK/mån)
      </a>
    </p>
  </div>
<?php elseif ($rtf_lang === 'en') : ?>
  <h1 class="section-title">Support Right to Family</h1>
  <p class="section-lead">
    If you want to support the work for legal certainty, transparency and professional handling of social cases,
    you can create a fixed monthly contribution. All payments are handled via Stripe for secure payment.
    All amounts are in DKK (Danish kroner).
  </p>
  <div class="info-box">
    <h3>Fixed monthly support</h3>
    <p>
      Using the button below you can support Ret til Familie in our analytical work and efforts to improve the area,
      with an amount between 20 and 100 DKK per month, until you cancel by writing to info@rettilfamilie.com.
    </p>
    <p style="margin-top:10px;">
      <a class="support-btn" href="https://buy.stripe.com/7sY8wQ4NA2i94Vn6z493y0b" target="_blank" rel="noopener">
        Support our work (20–100 DKK/month)
      </a>
    </p>
  </div>
<?php else : ?>
  <h1 class="section-title">Støt Ret til Familie</h1>
  <p class="section-lead">
    Hvis du ønsker at støtte arbejdet med retssikkerhed, gennemsigtighed og faglig korrekt håndtering af sociale sager,
    kan du oprette et fast månedligt bidrag. Alle betalinger håndteres via Stripe for sikker betaling.
  </p>
  <div class="info-box">
    <h3>Fast månedlig støtte</h3>
    <p>
      Via knappen nedenfor kan du støtte Ret til Familie i vores arbejde med analyser og forbedring af området,
      med et beløb mellem 20 og 100 kr. pr. måned, indtil du opsiger aftalen ved at skrive til info@rettilfamilie.com.
    </p>
    <p style="margin-top:10px;">
      <a class="support-btn" href="https://buy.stripe.com/7sY8wQ4NA2i94Vn6z493y0b" target="_blank" rel="noopener">
        Støt vores arbejde (20–100 kr./md)
      </a>
    </p>
  </div>
<?php
    endif;
    break;

  default:
    if (have_posts()) :
      while (have_posts()) : the_post(); ?>
        <h1 class="section-title"><?php the_title(); ?></h1>
        <div class="section-lead">
          <?php the_content(); ?>
        </div>
<?php
      endwhile;
    else : ?>
      <h1 class="section-title">Indhold ikke fundet</h1>
      <div class="section-lead">
        <p>Der blev ikke fundet indhold på denne adresse.</p>
      </div>
<?php
    endif;
    break;
endswitch;
?>
</div>
<?php get_footer(); ?>
