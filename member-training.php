<?php
declare(strict_types=1);

/** Aula única de primeiros passos. Disponível apenas para usuários com acesso ativo. */
function od_training_page(): never {
    $next = clinic() ? '?page=painel' : '?page=diagnostico';
    $nextText = clinic() ? 'Voltar ao painel' : 'Configurar minha clínica';
    h('Treinamento Odonto em Dia', false);
    echo '<div class="od-training-page">';
    echo '<header class="od-training-heading">'
       .'<a class="od-training-back" href="'.esc($next).'">← '.esc($nextText).'</a>'
       .'<span class="od-training-eyebrow">COMECE AQUI · VIDEOAULA</span>'
       .'<h1>Aprenda a usar o Odonto em Dia.</h1>'
       .'<p>Uma aula para conhecer o painel, cadastrar sua clínica, personalizar os documentos, salvar versões e preparar a pasta para impressão.</p>'
       .'</header>';
    echo '<section class="od-training-player" aria-label="Videoaula principal do Odonto em Dia">';
    echo '<div class="od-training-video" data-od-training-video>';
    echo '<button type="button" class="od-training-launch" data-od-training-launch aria-label="Reproduzir treinamento do Odonto em Dia">'
       .'<img src="/uploads/odonto-pasta-hero.webp" width="1672" height="941" alt="" loading="eager" decoding="async">'
       .'<span class="od-training-play-symbol" aria-hidden="true">'
       .'<svg viewBox="0 0 24 24" width="28" height="28"><path d="M9 5.7v12.6a1 1 0 0 0 1.55.83l9.15-6.3a1 1 0 0 0 0-1.66L10.55 4.87A1 1 0 0 0 9 5.7Z" fill="currentColor"/></svg></span>'
       .'<span class="od-training-launch-caption">Assistir treinamento</span>'
       .'</button>';
    echo '</div>';
    echo '<div class="od-training-under">'
       .'<span>Assista no seu ritmo. Você pode voltar a esta aula sempre que precisar.</span>'
       .'<a href="https://youtu.be/VQEmTrlaotk" rel="noopener noreferrer" target="_blank">Abrir no YouTube ↗</a>'
       .'</div>';
    echo '</section>';
    echo '<nav class="od-training-next" aria-label="Próximos passos">'
       .'<a class="od-training-next-main" href="'.esc($next).'">'.esc($nextText).' <span aria-hidden="true">→</span></a>'
       .'<a class="od-training-next-secondary" href="?page=suporte">Precisa de ajuda?</a>'
       .'</nav>';
    echo '</div>';
    foot();exit;
}
