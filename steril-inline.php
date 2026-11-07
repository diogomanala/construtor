<?php
declare(strict_types=1);
/**
 * Guia fotográfico de apoio incorporado ao POP 08, sem seleções de materiais
 * salvas e sem modificar o texto versionado da minuta. 07/11/2026.
 * A cena é conceitual; validação clínica profissional ainda pendente.
 */
function od_steril_inline_html(): string {
    $items=od_steril_visual_items();
    $points=[
        'autoclave'=>[15.6,16.2],
        'seladora'=>[53.1,37.7],
        'embalagem'=>[80.1,29.3],
        'quimico'=>[25.6,74.0],
        'biologico'=>[35.7,72.3],
        'bandeja'=>[46.8,72.1],
        'cassete'=>[71.7,69.5],
    ];
    $first=$items['autoclave'];
    ob_start();
    ?>
    <section class="si-doc-illustration" id="odx-esteril-visual" aria-labelledby="si-title">
      <header class="si-header">
        <span class="si-eyebrow">REFERÊNCIA VISUAL · ESTERILIZAÇÃO</span>
        <h3 id="si-title">Materiais e equipamentos ilustrados</h3>
        <p>Selecione um número na imagem ou na lista para identificar o objeto. Este quadro é um apoio à leitura do POP.</p>
      </header>
      <div class="si-layout">
        <div class="si-visual-wrap">
          <div class="si-scene">
            <img src="assets/illustrations/sterilization-bench-photo-v1.avif"
                 width="977" height="483"
                 alt="Bancada ilustrativa com sete materiais numerados: autoclave, seladora, embalagem, indicador químico, indicador biológico, bandeja perfurada e cassete de instrumentais."
                 loading="lazy" decoding="async">
            <?php foreach($points as $key=>$xy):
                $name=$items[$key]['name'];
                $number=(int)array_search($key,array_keys($points),true)+1;
                ?>
                <button type="button"
                        class="si-hotspot<?php echo $number===1?' is-current':''; ?>"
                        data-si-key="<?php echo esc($key); ?>"
                        data-si-name="<?php echo esc($name); ?>"
                        data-si-kind="<?php echo esc($items[$key]['kind']); ?>"
                        data-si-description="<?php echo esc($items[$key]['description']); ?>"
                        data-si-check="<?php echo esc($items[$key]['check']); ?>"
                        data-si-number="<?php echo $number; ?>"
                        aria-label="<?php echo esc('Ver objeto '.$number.': '.$name); ?>"
                        aria-pressed="<?php echo $number===1?'true':'false'; ?>"
                        style="left:<?php echo $xy[0]; ?>%;top:<?php echo $xy[1]; ?>%"></button>
            <?php endforeach; ?>
          </div>
          <p class="si-photo-caption">Representação ilustrativa. Equipamentos, acessórios e produtos devem corresponder à rotina real da clínica e às instruções dos fabricantes.</p>
        </div>
        <div class="si-detail" data-si-detail aria-live="polite">
          <span class="si-detail-index" data-si-number>01 / 07</span>
          <div class="si-detail-photo" data-si-photo role="img" aria-label="Detalhe ilustrativo do equipamento selecionado"></div>
          <span class="si-detail-category" data-si-kind><?php echo esc($first['kind']); ?></span>
          <h4 data-si-name><?php echo esc($first['name']); ?></h4>
          <p data-si-description><?php echo esc($first['description']); ?></p>
          <div class="si-detail-check">
            <strong>O que conferir</strong>
            <p data-si-check><?php echo esc($first['check']); ?></p>
          </div>
        </div>
      </div>
      <div class="si-legend">
        <strong class="si-legend-title">Identificação dos objetos</strong>
        <ol>
          <?php $n=0;foreach($items as $key=>$row):$n++;?>
            <li><button type="button" class="si-list-button<?php echo $n===1?' is-current':''; ?>"
              data-si-key="<?php echo esc($key); ?>"
              aria-pressed="<?php echo $n===1?'true':'false'; ?>">
              <span><?php echo str_pad((string)$n,2,'0',STR_PAD_LEFT); ?></span>
              <?php echo esc($row['name']); ?>
            </button></li>
          <?php endforeach;?>
        </ol>
      </div>
      <p class="si-disclaimer">As imagens são conceituais. Este material não estabelece parâmetros de esterilização, composição obrigatória nem conformidade sanitária. A revisão técnica do conteúdo continua necessária.</p>
    </section>
    <?php
    return (string)ob_get_clean();
}
