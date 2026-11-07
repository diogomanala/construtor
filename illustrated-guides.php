<?php
declare(strict_types=1);

/**
 * Guias visuais por documento. Padrão: cena + hotspots numerados + imagem isolada
 * nítida, com legendas contextualizadas. Não altera minutas versionadas.
 *
 * Os guias novos só aparecem após aprovação de TODOS os assets relevantes.
 * O POP 08 já utiliza implementação própria e aprovada em steril-inline.php.
 */
function od_illustrated_catalog(): array {
    static $cache=null;
    if($cache!==null)return $cache;
    $json=@file_get_contents(__DIR__.'/data/illustrated-guides.json');
    $data=is_string($json)?json_decode($json,true):null;
    return $cache=is_array($data)?($data['guides']??[]):[];
}

function od_illustrated_asset(string $path): bool {
    if(!preg_match('~^assets/illustrations/[a-z0-9/_-]+\.(?:webp|avif|png)$~D',$path))return false;
    $root=realpath(__DIR__.'/assets/illustrations');
    $file=realpath(__DIR__.'/'.$path);
    return $root!==false && $file!==false && str_starts_with($file,$root.DIRECTORY_SEPARATOR) && is_file($file);
}

function od_illustrated_ready(array $guide): bool {
    if(($guide['status']??'')!=='aprovado_visualmente')return false;
    if(empty($guide['items'])||!is_array($guide['items'])||count($guide['items'])>12)return false;
    if(!is_array($guide['scene']??null)||!od_illustrated_asset((string)($guide['scene']['asset']??'')))return false;
    $unique=[];
    foreach($guide['items'] as $item){
        if(!is_array($item))return false;
        $key=(string)($item['key']??'');
        if(!preg_match('/^[a-z0-9_]{2,40}$/D',$key)||isset($unique[$key]))return false;
        $unique[$key]=true;
        if(!od_illustrated_asset((string)($item['asset']??'')))return false;
        $pos=$item['point']??null;
        if(!is_array($pos)||count($pos)!==2||!isset($pos[0],$pos[1]))return false;
        foreach($pos as $value)if(!is_numeric($value)||$value<5||$value>95)return false;
        if(trim((string)($item['name']??''))==='')return false;
    }
    return true;
}

function od_illustrated_guide_for_document(int $documentId): ?array {
    foreach(od_illustrated_catalog() as $guide){
        if(!is_array($guide)||!in_array($documentId,$guide['document_ids']??[],true))continue;
        return od_illustrated_ready($guide)?$guide:null;
    }
    return null;
}

function od_illustrated_guide_html(int $documentId): string {
    $guide=od_illustrated_guide_for_document($documentId);
    if(!$guide)return '';
    $items=$guide['items'];
    $first=$items[0];
    $id='odx-visual-guide-'.(string)$guide['key'];
    $scene=$guide['scene'];
    ob_start();
    ?>
    <section class="si-doc-illustration" id="<?php echo esc($id); ?>"
      data-gid-guide aria-labelledby="<?php echo esc($id); ?>-title">
      <header class="si-header">
        <span class="si-eyebrow"><?php echo esc($guide['eyebrow']??'REFERÊNCIA VISUAL'); ?></span>
        <h3 id="<?php echo esc($id); ?>-title"><?php echo esc($guide['title']); ?></h3>
        <p><?php echo esc($guide['description']); ?> Toque nos números ou na lista para identificar cada item.</p>
      </header>
      <div class="si-layout">
        <div class="si-visual-wrap">
          <div class="si-scene">
            <img src="<?php echo esc($scene['asset']); ?>"
              alt="<?php echo esc($scene['alt']??'Conjunto ilustrativo de materiais'); ?>"
              width="<?php echo (int)($scene['width']??1200); ?>"
              height="<?php echo (int)($scene['height']??600); ?>"
              loading="lazy" decoding="async">
            <?php foreach($items as $i=>$item): ?>
              <button class="si-hotspot<?php echo $i===0?' is-current':''; ?>"
                type="button" data-gid-object="<?php echo esc($item['key']); ?>"
                data-gid-name="<?php echo esc($item['name']); ?>"
                data-gid-kind="<?php echo esc($item['kind']??'Material'); ?>"
                data-gid-description="<?php echo esc($item['description']??''); ?>"
                data-gid-check="<?php echo esc($item['check']??''); ?>"
                data-gid-image="<?php echo esc($item['asset']); ?>"
                data-gid-number="<?php echo $i+1; ?>"
                aria-label="<?php echo esc('Ver objeto '.($i+1).': '.$item['name']); ?>"
                aria-pressed="<?php echo $i===0?'true':'false'; ?>"
                style="left:<?php echo (float)$item['point'][0]; ?>%;top:<?php echo (float)$item['point'][1]; ?>%">
              </button>
            <?php endforeach; ?>
          </div>
          <p class="si-photo-caption">Imagens ilustrativas. Os materiais devem corresponder à estrutura e à rotina real da clínica.</p>
        </div>
        <aside class="si-detail" data-gid-detail aria-live="polite">
          <span class="si-detail-index" data-gid-number>01 / <?php echo str_pad((string)count($items),2,'0',STR_PAD_LEFT); ?></span>
          <figure class="si-detail-photo">
            <img src="<?php echo esc($first['asset']); ?>" data-gid-photo
              alt="<?php echo esc($first['name'].' isolado, sem fundo'); ?>"
              width="810" height="810" loading="lazy" decoding="async">
            <figcaption class="si-photo-badge">OBJETO SELECIONADO</figcaption>
          </figure>
          <span class="si-detail-category" data-gid-kind><?php echo esc($first['kind']??'Material'); ?></span>
          <h4 data-gid-name><?php echo esc($first['name']); ?></h4>
          <p data-gid-description><?php echo esc($first['description']??''); ?></p>
          <div class="si-detail-check">
            <strong>O que conferir</strong>
            <p data-gid-check><?php echo esc($first['check']??''); ?></p>
          </div>
        </aside>
      </div>
      <nav class="si-legend" aria-label="Itens deste quadro">
        <strong class="si-legend-title">Identificação dos objetos</strong>
        <ol>
          <?php foreach($items as $i=>$item): ?>
            <li><button type="button"
              class="si-list-button<?php echo $i===0?' is-current':''; ?>"
              data-gid-list="<?php echo esc($item['key']); ?>"
              aria-pressed="<?php echo $i===0?'true':'false'; ?>">
              <span><?php echo str_pad((string)($i+1),2,'0',STR_PAD_LEFT); ?></span>
              <?php echo esc($item['name']); ?>
            </button></li>
          <?php endforeach; ?>
        </ol>
      </nav>
      <p class="si-disclaimer">Conteúdo ilustrativo de referência, não representa uma lista obrigatória nem comprova conformidade sanitária. Requer revisão do responsável técnico.</p>
    </section>
    <?php
    return (string)ob_get_clean();
}
