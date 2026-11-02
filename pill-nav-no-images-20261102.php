<?php
// CIC minimalist home and lightweight pill navigation, 2026-11-02.
// Guarded CLI patch. Keeps the image splitter form and PHP untouched.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

$root='/home/u783725167/domains/cortarimagenscarrossel.online/public_html';
$home=$root.'/index.html';
$css=$root.'/style.css';
$backup=dirname($root).'/cic-adsense-backup-20261102/pill-nav-minimal-home';

$html=@file_get_contents($home);
$styles=@file_get_contents($css);
if (!is_string($html)||!is_string($styles)) { echo "READ_ERROR\n"; exit(1); }
if (strpos($html,'CIC_PILL_NAV_20261102')!==false) { echo "ALREADY_APPLIED\n"; exit; }

$modified=$html;
$imagesBefore=substr_count($modified,'<img ');
if ($imagesBefore<4) { echo "UNEXPECTED_IMAGE_COUNT\n"; exit(1); }

// Remove the oversized demonstration gallery. The original examples remain
// on /exemplos-de-corte.html, linked from the pill nav.
$pattern='~\s*<section class="cic-evidence"[^>]*>[\s\S]*?</section>\s*~';
$modified=preg_replace($pattern,"\n",$modified,1,$count);
if ($count!==1) { echo "GALLERY_ANCHOR_ERROR ".$count."\n"; exit(1); }

// Remove old illustrated 3-step row without modifying the underlying form.
$start=strpos($modified,'<div class="step-by-step">');
$end=strpos($modified,'<!-- ============================= -->',$start?:0);
if ($start===false||$end===false||$end<=$start) {
 echo "STEPS_ANCHOR_ERROR\n"; exit(1);
}
$modified=substr($modified,0,$start)."\n".substr($modified,$end);

// Remove second large infographic.
$pattern='~\s*<figure id="bmd-banner-visual-20261031"[^>]*>[\s\S]*?</figure>\s*~';
$modified=preg_replace($pattern,"\n",$modified,1,$count);
if ($count!==1) { echo "INFOGRAPHIC_ANCHOR_ERROR ".$count."\n"; exit(1); }

$nav=<<<'HTML'
<!-- CIC_PILL_NAV_20261102 -->
<nav class="cic-nav" aria-label="Navegação principal">
  <a class="cic-brand" href="/" aria-label="Cortar Carrossel, página inicial">
    <span class="cic-brand-mark" aria-hidden="true"><span></span><span></span><span></span></span>
    <span>Cortar Carrossel</span>
  </a>
  <div class="cic-navlinks">
    <a href="/" aria-current="page">Cortador</a>
    <a href="/calculadora-carrossel.html">Calculadora</a>
    <a href="/exemplos-de-corte.html">Exemplos</a>
    <a href="/guias/">Guias</a>
    <a href="/sobre.html">Sobre</a>
  </div>
</nav>
HTML;
$modified=preg_replace('~<nav class="cic-nav"[^>]*>[\s\S]*?</nav>~',$nav,$modified,1,$count);
if ($count!==1){echo "NAV_ANCHOR_ERROR ".$count."\n";exit(1);}
$modified=str_replace('<link rel="stylesheet" href="style.css">','<link rel="stylesheet" href="style.css?v=20261102p">',$modified);

$marker='/* CIC_ADSENSE_20261102 */';
if(substr_count($styles,$marker)!==1) {echo "CSS_ANCHOR_ERROR\n";exit(1);}

$newCss=<<<'CSS'
/* CIC_PILL_NAV_20261102: zero JS, zero external fonts, no decorative images */
body > .cic-nav {
  width: 100%;
  max-width: 900px;
  min-height: 58px;
  margin: 0 auto 27px;
  padding: 8px 10px 8px 17px;
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  background: #fff;
  border: 1px solid #e2e7ef;
  border-radius: 999px;
  box-shadow: 0 7px 22px rgba(23,40,64,.055);
}
body > .cic-nav .cic-brand {
  display: inline-flex;
  align-items: center;
  gap: 9px;
  color: #17253d;
  font-size: 14px;
  font-weight: 800;
  letter-spacing: -.025em;
  text-decoration: none;
  white-space: nowrap;
}
.cic-brand-mark {
  width: 27px;
  height: 27px;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 2px;
  padding: 5px;
  border-radius: 9px;
  background: #2856e7;
}
.cic-brand-mark span {
  display: block;
  flex: 1;
  height: 100%;
  border-radius: 1px;
  border: 1px solid rgba(255,255,255,.75);
}
body > .cic-nav .cic-navlinks {
  display: flex;
  align-items: center;
  gap: 2px;
  flex-wrap: nowrap;
}
body > .cic-nav .cic-navlinks a {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-height: 36px;
  padding: 8px 12px;
  border-radius: 999px;
  color: #44516a;
  font-size: 13px;
  line-height: 1.2;
  font-weight: 600;
  text-decoration: none;
  white-space: nowrap;
  transition: color .15s ease, background-color .15s ease;
}
body > .cic-nav .cic-navlinks a:hover {
  color: #1e3f9d;
  background: #f3f6fc;
}
body > .cic-nav .cic-navlinks a[aria-current="page"] {
  color: #234eaf;
  background: #eaf0ff;
  font-weight: 750;
}
body > .cic-nav a:focus-visible {
  outline: 2px solid #2856e7;
  outline-offset: 2px;
}
body > h1 {
  max-width: 680px;
  font-size: clamp(24px,3.8vw,32px);
  line-height: 1.18;
  letter-spacing: -.035em;
  color: #19273e;
  margin-bottom: 10px;
}
.cic-lead {
  max-width: 640px;
  margin: 0 auto 26px;
  color: #536278;
  text-align: center;
  font-size: 15px;
  line-height: 1.55;
}
body > .container {
  border-radius: 14px;
  box-shadow: 0 8px 26px rgba(23,40,64,.07);
  border: 1px solid #e3e8f0;
}
body > .container form { width: 100%; }
body > .container button[type="submit"] {
  background: #2856e7;
  border-radius: 9px;
  font-weight: 700;
  min-height: 44px;
}
body > .container button[type="submit"]:hover {
  background: #1f46c1;
}
body > .container button[type="submit"]:focus-visible {
  outline: 2px solid #2856e7;
  outline-offset: 3px;
}
@media (max-width: 720px) {
  body > .cic-nav {
    width: 100%;
    min-height: 0;
    padding: 10px 10px 7px;
    gap: 7px;
    flex-direction: column;
    align-items: stretch;
    border-radius: 20px;
    margin-bottom: 23px;
  }
  body > .cic-nav .cic-brand {
    min-height: 31px;
    justify-content: center;
  }
  body > .cic-nav .cic-navlinks {
    width: 100%;
    overflow-x: auto;
    justify-content: flex-start;
    scrollbar-width: none;
    padding: 0 0 3px;
    -webkit-overflow-scrolling: touch;
  }
  body > .cic-nav .cic-navlinks::-webkit-scrollbar { display: none; }
  body > .cic-nav .cic-navlinks a {
    flex: 0 0 auto;
    min-height: 33px;
    padding: 7px 11px;
    font-size: 12px;
  }
}
@media (prefers-reduced-motion: reduce) {
  body > .cic-nav .cic-navlinks a { transition: none; }
}
CSS;
$styles=substr($styles,0,strpos($styles,$marker))."\n".$newCss."\n";

// Preserve the data path while removing every image from home.
if (preg_match('/<img\b/i',$modified) ||
    substr_count($modified,'id="imageForm"')!==1 ||
    substr_count($modified,'action="process.php"')!==1 ||
    substr_count($modified,'<nav class="cic-nav"')!==1 ||
    substr_count($modified,'cic-brand-mark')!==1 ||
    strpos($modified,'Perguntas frequentes (FAQ)')===false ||
    strpos($modified,'Qual ferramenta usar para cada tipo de corte?')===false ||
    strpos($modified,'/exemplos-de-corte.html')===false ||
    strpos($modified,'adsbygoogle.js')===false) {
 echo "HTML_VALIDATION_ERROR\n";exit(1);
}
if (strlen($styles)>20000||strpos($styles,'CIC_PILL_NAV_20261102')===false) {
 echo "CSS_VALIDATION_ERROR\n";exit(1);
}
if (!is_dir($backup)&&!mkdir($backup,0750,true)){echo "BACKUP_DIR_ERROR\n";exit(1);}
foreach(['index.html'=>$home,'style.css'=>$css] as $key=>$path){
 if(!file_exists($backup.'/'.$key)&&!copy($path,$backup.'/'.$key)){
  echo "BACKUP_ERROR ".$key."\n";exit(1);
 }
}
if (file_put_contents($css,$styles,LOCK_EX)===false) {
 echo "CSS_WRITE_ERROR\n";exit(1);
}
if (file_put_contents($home,$modified,LOCK_EX)===false) {
 copy($backup.'/style.css',$css);
 echo "HTML_WRITE_ERROR; CSS_ROLLED_BACK\n";exit(1);
}
echo "PILL_NAV_DONE; HOME_IMAGES_REMOVED; FORM_PRESERVED; CSS_BYTES=".strlen($styles)."\n";
