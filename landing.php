<?php declare(strict_types=1); ?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="Odonto em Dia: Trilha Documental Guiada para clínicas odontológicas, com autoavaliação inicial, 30 modelos, editor interativo, versões salvas e acompanhamento. Acesso por R$ 67.">
<meta name="theme-color" content="#0c2e35">
<link rel="icon" type="image/svg+xml" href="assets/favicon.svg?v=8">
<title>Odonto em Dia | RDC 1.002/2025 | Kit e painel online por R$ 67</title>
<link rel="stylesheet" href="sales.css?v=docsys1-20261106">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/hero-motion-v1.css?v=20261108">
<link rel="stylesheet" href="assets/hero-mobile-v2.css?v=20261108b">
</head>
<body class="sales">
<svg style="position:absolute;width:0;height:0;overflow:hidden" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
<symbol id="ic-check" viewBox="0 0 24 24"><path d="m5 12 4 4L19 6"/></symbol>
<symbol id="ic-arrow" viewBox="0 0 24 24"><path d="M5 12h14m-6-6 6 6-6 6"/></symbol>
<symbol id="ic-shield" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/></symbol>
<symbol id="ic-file" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6M8 13h8M8 17h8"/></symbol>
<symbol id="ic-steth" viewBox="0 0 24 24"><path d="M6 3v7a6 6 0 0 0 12 0V3M6 3H4v4h2m12-4h2v4h-2M12 16v2a4 4 0 0 0 8 0v-2"/><circle cx="20" cy="14" r="2"/></symbol>
<symbol id="ic-clipboard" viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="18" rx="2"/><path d="M9 2h6v4H9zM8 12l2 2 4-4M8 18h8"/></symbol>
<symbol id="ic-user" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 22v-2a8 8 0 0 1 16 0v2"/></symbol>
<symbol id="ic-search" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m16 16 5 5"/></symbol>
<symbol id="ic-progress" viewBox="0 0 24 24"><path d="M12 2a10 10 0 1 0 10 10H12Z"/><path d="M12 2v10h10A10 10 0 0 0 12 2Z"/></symbol>
<symbol id="ic-download" viewBox="0 0 24 24"><path d="M12 3v13m-5-5 5 5 5-5M4 17v4h16v-4"/></symbol>
<symbol id="ic-clock" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l4 2"/></symbol>
<symbol id="ic-spark" viewBox="0 0 24 24"><path d="m12 2 2.6 7.4L22 12l-7.4 2.6L12 22l-2.6-7.4L2 12l7.4-2.6Z"/></symbol>
<symbol id="ic-lock" viewBox="0 0 24 24"><rect x="4" y="10" width="16" height="12" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></symbol>
<symbol id="ic-close" viewBox="0 0 24 24"><path d="M6 6 18 18M18 6 6 18"/></symbol>
<symbol id="ic-book" viewBox="0 0 24 24"><path d="M4 5a2 2 0 0 1 2-2h13v17H6a2 2 0 0 0-2 2Zm0 0v17M8 8h7M8 12h8"/></symbol>
<symbol id="ic-monitor" viewBox="0 0 24 24"><rect x="2" y="3" width="20" height="15" rx="2"/><path d="M8 22h8m-4-4v4"/></symbol>
<symbol id="ic-list" viewBox="0 0 24 24"><path d="m3 6 1 1 2-2m3 1h12M3 12l1 1 2-2m3 1h12M3 18l1 1 2-2m3 1h12"/></symbol>
</svg>
<?php function si(string $id,string $class='icon'):string {return '<svg class="'.htmlspecialchars($class,ENT_QUOTES).'" aria-hidden="true"><use href="#ic-'.htmlspecialchars($id,ENT_QUOTES).'"></use></svg>';} ?>
<div class="reg-bar"><div class="wrap"><span class="reg-status"></span><strong>RDC ANVISA 1.002/2025 em vigor.</strong><span>Prazo de adequação dos serviços existentes previsto para dezembro de 2026.</span> <a href="#base-legal">Confira a base normativa <?=si('arrow')?></a></div></div>
<header class="site-nav"><div class="wrap nav-inner"><a href="?page=inicio" class="logo" aria-label="Odonto em Dia, início"><img class="brand-mark" src="assets/favicon.svg" width="30" height="30" alt=""><span>odonto<span class="logo-accent">em dia</span><small>GESTÃO DOCUMENTAL</small></span></a><nav aria-label="Navegação principal"><a href="#metodo">Como funciona</a><a href="#entrega">O que recebo</a><a href="#duvidas">Perguntas</a></nav><div class="nav-actions"><a href="?page=entrar" class="text-login">Entrar</a><a href="#entrega" class="btn btn-small">O que vem no kit <?=si('arrow')?></a></div></div></header>
<main>
<section class="hero"><div class="wrap hero-layout">
<div class="hero-copy">
 <div class="pill-label"><?=si('shield')?> TRILHA DOCUMENTAL GUIADA · RDC 1.002/2025</div>
 <h1>Organize os documentos da sua clínica <span>passo a passo.</span></h1>
 <p>Comece pela autoavaliação, siga uma trilha simples e prepare os 30 modelos no <strong>editor interativo da plataforma</strong>. Acompanhe pendências e monte sua pasta A4 com capa, índice e divisórias para imprimir após revisão técnica.</p>
 <div class="hero-buttons"><a class="btn btn-large" href="?page=checkout">Organizar meus documentos · R$ 67 <?=si('arrow')?></a><a class="btn btn-outline btn-large" href="#entrega">Ver tudo que está incluído <?=si('book')?></a></div>
 <div class="hero-footline"><span><?=si('check')?> Trilha guiada</span><span><?=si('check')?> Editor com versões salvas</span><span><?=si('check')?> Pasta A4 para impressão</span></div>
 <p class="tiny">Modelos de referência para adaptar e revisar com o responsável técnico. A plataforma não certifica conformidade sanitária.</p>
</div>
<div class="hero-media">
<div class="od-visual od-motion" data-od-motion>
<div aria-label="Ilustração da plataforma Odonto em Dia: biblioteca com 30 materiais, editor preenchendo o nome da clínica, versão salva, painel de acompanhamento e pasta documental impressa." class="od-stage" id="odStage" role="group">
<div aria-hidden="true" class="od-s">
<div class="od-glow"></div>
<div class="od-rings"></div>
<div class="od-floor"></div>
<div class="od-scene">
<!-- Folha PGRSS (fundo) -->
<div class="od-obj od-sheet od-sheet-b">
<div class="od-shh"><span class="od-shl"><b>CA</b></span><span class="od-shn">Clínica Aurora</span><span class="od-shc">PGRSS</span></div>
<p class="od-shk">PLANO</p>
<p class="od-sht">Resíduos</p>
<span class="od-l od-w95"></span><span class="od-l od-w88"></span><span class="od-l od-w70"></span>
<div class="od-shbox"></div>
</div>
<!-- Folha POP (frente) -->
<div class="od-obj od-sheet od-sheet-a">
<div class="od-shh"><span class="od-shl"><b>CA</b></span><span class="od-shn">Clínica Aurora</span><span class="od-shc">POP</span></div>
<p class="od-shk">PROCEDIMENTO OPERACIONAL PADRÃO</p>
<p class="od-sht">Esterilização</p>
<span class="od-l od-w95"></span><span class="od-l od-w80"></span><span class="od-l od-w88"></span><span class="od-l od-w60"></span>
<div class="od-shbox"></div>
</div>
<!-- Pasta documental -->
<div class="od-obj od-pasta">
<div class="od-pasta-tabs"><span>POPs</span><span>Planos</span><span>Formulários</span></div>
<div class="od-pasta-paper"></div>
<div class="od-pasta-cover">
<span class="od-rivet" style="top:22%"></span><span class="od-rivet" style="top:74%"></span>
<div class="od-pasta-txt">
<p class="od-pk">PASTA<br/>DOCUMENTAL</p>
<p class="od-pn">Clínica<br/>Aurora</p>
<p class="od-ps">Odontologia</p>
<span class="od-pr"></span>
<p class="od-pi">POPs · Planos<br/>Formulários · Guia</p>
</div>
<p class="od-pf">GESTÃO DOCUMENTAL</p>
<span class="od-sheen"></span>
</div>
</div>
<!-- Tablet com a plataforma -->
<div class="od-obj od-tablet">
<div class="od-screen">
<aside class="od-side">
<div class="od-sbrand"><i></i><span>odonto<br/>em dia</span></div>
<div class="od-snav">
<span class="od-sn"><i></i>Painel</span>
<span class="od-sn is-on"><i></i>Biblioteca</span>
<span class="od-sn"><i></i>Pendências</span>
<span class="od-sn"><i></i>Autoavaliação</span>
</div>
<div class="od-sfoot"><b>CA</b><span></span></div>
</aside>
<div class="od-main">
<!-- Tela 1: Biblioteca -->
<section class="od-scr od-scr-lib">
<div class="od-head">
<div><p class="od-kick">Biblioteca</p><p class="od-h3">Materiais de referência</p></div>
<span class="od-chip">30 materiais</span>
</div>
<div class="od-cats">
<span class="od-cat is-on">Todos</span>
<span class="od-cat">POPs<b>18</b></span>
<span class="od-cat">Planos<b>7</b></span>
<span class="od-cat">SDBPF<b>2</b></span>
<span class="od-cat">Formulários<b>2</b></span>
<span class="od-cat">Guia<b>1</b></span>
</div>
<div class="od-grid">
<div class="od-doc od-doc-t"><span class="od-dic"></span><b>POP · Esterilização</b><span class="od-tag">POP</span><span class="od-tap od-tap-1"></span></div>
<div class="od-doc"><span class="od-dic"></span><b>PGRSS</b><span class="od-tag">PLANO</span></div>
<div class="od-doc"><span class="od-dic"></span><b>POP · Higienização</b><span class="od-tag">POP</span></div>
<div class="od-doc"><span class="od-dic"></span><b>Plano de manutenção</b><span class="od-tag">PLANO</span></div>
<div class="od-doc"><span class="od-dic"></span><b>Manual-mestre</b><span class="od-tag">SDBPF</span></div>
<div class="od-doc"><span class="od-dic"></span><b>Termo de ciência da equipe</b><span class="od-tag">FORMULÁRIO</span></div>
</div>
</section>
<!-- Tela 2: Editor -->
<section class="od-scr od-scr-ed">
<div class="od-edbar">
<span class="od-back"><svg fill="none" viewbox="0 0 16 16"><path d="M10 3.5 5.5 8l4.5 4.5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg></span>
<div class="od-edt"><p class="od-kick">Editor</p><p class="od-h3">POP · Esterilização</p></div>
<div class="od-edact">
<span class="od-chip od-ver"><span class="od-v3">Versão 3</span><span class="od-v4">Versão 4</span></span>
<span class="od-chip od-chip-o">DOCX</span>
<span class="od-chip od-chip-o">PDF</span>
</div>
</div>
<div class="od-edbody">
<div class="od-fields">
<div class="od-fld od-fld-t">
<span class="od-fl">Nome da clínica</span>
<span class="od-in od-in-t"><span class="od-typed">Clínica Aurora</span><span class="od-caret"><i></i></span><span class="od-tap od-tap-2"></span></span>
</div>
<div class="od-fld"><span class="od-flb"></span><span class="od-in"><span class="od-ph"></span></span></div>
<div class="od-fld"><span class="od-flb"></span><span class="od-in"><span class="od-ph" style="width:42%"></span></span></div>
<div class="od-note"><i></i><div style="flex:1"><span style="width:90%"></span><span style="width:60%;margin-top:.4em"></span></div></div>
</div>
<div class="od-page">
<div class="od-pgh">
<span class="od-pglogo"><span class="od-a"></span><span class="od-b"><b>CA</b></span></span>
<span class="od-pgn"><span class="od-pgph"></span><span class="od-pgreal">Clínica Aurora</span></span>
</div>
<p class="od-pgk">PROCEDIMENTO OPERACIONAL PADRÃO</p>
<p class="od-pgt">Esterilização</p>
<span class="od-l od-w95"></span><span class="od-l od-w88"></span><span class="od-l od-w70"></span>
<span class="od-l od-w95"></span><span class="od-l od-w45"></span>
<div class="od-pgtab"></div>
</div>
</div>
</section>
<div class="od-toast"><i></i>Versão salva</div>
</div>
</div>
</div>
<!-- Card de acompanhamento -->
<div class="od-obj od-pend">
<div class="od-pend-in">
<div class="od-ph-row">
<span class="od-pend-k">ACOMPANHAMENTO</span>
<span class="od-pend-c"><span class="od-num"><span class="od-n11">11</span><span class="od-n12">12</span></span> de 17 frentes</span>
</div>
<div class="od-bar"><span class="od-fill"></span></div>
<ul class="od-pl">
<li class="od-done"><span class="od-ck"></span><span class="od-lb">PGRSS</span><span class="od-who"><b>AL</b></span></li>
<li class="od-it"><span class="od-ck"></span><span class="od-lb">POP de esterilização</span><span class="od-who"><b>MR</b></span></li>
<li><span class="od-ck"></span><span class="od-lb">Revisão pelo RT</span><span class="od-due">PENDENTE</span></li>
</ul>
</div>
</div>
</div>
</div>
</div>
<button aria-label="Pausar animação" aria-pressed="false" class="od-toggle" id="odToggle" type="button">
<svg aria-hidden="true" class="od-ic-pause" viewbox="0 0 12 12"><rect fill="currentColor" height="9" rx=".8" width="2.6" x="2" y="1.5"></rect><rect fill="currentColor" height="9" rx=".8" width="2.6" x="7.4" y="1.5"></rect></svg>
<svg aria-hidden="true" class="od-ic-play" viewbox="0 0 12 12"><path d="M3 1.8v8.4c0 .5.5.8.9.5l6.4-4.2a.6.6 0 0 0 0-1L3.9 1.3c-.4-.3-.9 0-.9.5Z" fill="currentColor"></path></svg>
</button>
<p class="od-motion-caption">Prévia ilustrativa do produto</p>
</div>
</div>
</div></section>
<section class="kit-clarity" aria-label="Entenda exatamente o produto"><div class="wrap"><div class="kit-clarity-head"><span class="sec-label">Trilha Documental Guiada</span><h2>Saiba por onde começar. <em>Prepare e acompanhe cada etapa.</em></h2><p>Uma sequência de uso com autoavaliação, modelos editáveis e acompanhamento. Prepare os documentos na plataforma, consulte as referências oficiais e reúna uma pasta A4 organizada para imprimir após a revisão do responsável técnico.</p></div><div class="kit-clarity-grid">
<article class="kit-clarity-card"><div class="kit-clarity-icon"><?=si('book')?></div><div><h3>1. Veja por onde começar</h3><p>Faça a <strong>autoavaliação inicial de 15 pontos</strong> e siga a trilha de primeiros passos para organizar a documentação da clínica.</p></div></article>
<article class="kit-clarity-card"><div class="kit-clarity-icon"><?=si('file')?></div><div><h3>2. Edite dentro da plataforma</h3><p>Escolha entre <strong>30 materiais de referência</strong>, preencha os dados guiados e salve versões para revisão técnica.</p></div></article>
<article class="kit-clarity-card"><div class="kit-clarity-icon"><?=si('progress')?></div><div><h3>3. Revise e prepare para imprimir</h3><p>Acompanhe pendências, consulte fontes oficiais e monte uma <strong>pasta A4 com capa, índice e divisórias</strong>. A aprovação técnica é feita pelo RT.</p></div></article>
</div><p class="kit-clarity-note">Seu acesso online permanece disponível por 12 meses. Os modelos e versões ficam organizados no ambiente da clínica.</p></div></section>
<section class="facts"><div class="wrap facts-grid"><div><b>30</b><span>materiais na biblioteca</span></div><div><b>18</b><span>minutas de POPs</span></div><div><b>17</b><span>frentes de acompanhamento</span></div><div><b>15</b><span>verificações iniciais</span></div></div></section>

<section class="space deliver" id="entrega"><div class="wrap"><div class="section-heading centered"><span class="sec-label">O que você recebe</span><h2>Edite, salve e prepare para imprimir. <em>Tudo na plataforma.</em></h2><p>A biblioteca guarda os materiais e o editor registra suas versões. Você pode voltar ao trabalho quando quiser e acompanhar as pendências. Os modelos precisam de adaptação e revisão pelo responsável técnico.</p></div><div class="deliver-grid"><article class="deliver-highlight"><span class="mini-label"><?=si('monitor')?> INCLUÍDO NO KIT DE R$ 67</span><h3>Seu editor e sua pasta documental A4</h3><p>Prepare minutas no editor, salve versões e reúna documentos em uma pasta com capa personalizada, índice e divisórias, pronta para a etapa de revisão e impressão.</p><div class="deliver-highlight-list"><span><?=si('check')?> Onboarding guiado em três etapas</span><span><?=si('check')?> 17 frentes de acompanhamento</span><span><?=si('check')?> Responsáveis, prazos e observações</span><span><?=si('check')?> Autoavaliação inicial com 15 verificações</span><span><?=si('check')?> Pasta A4 para impressão, com capa, índice e divisórias</span></div><a href="?page=amostra" class="inline-link">Ver uma amostra real de minuta <?=si('arrow')?></a></article><div class="deliver-side"><article><span class="small-icon"><?=si('file')?></span><div><strong>18 POPs</strong><p>Minutas de rotinas operacionais, incluindo higienização, processamento de produtos, resíduos e segurança.</p></div></article><article><span class="small-icon"><?=si('clipboard')?></span><div><strong>7 planos estruturados</strong><p>Modelos para organizar PSP, PP-IRAS, PGRSS, PGTS, água, educação permanente e manutenção.</p></div></article><article><span class="small-icon"><?=si('book')?></span><div><strong>2 materiais de SDBPF</strong><p>Mapa dos itens do artigo 113 e estrutura de manual-mestre para preenchimento.</p></div></article><article><span class="small-icon"><?=si('list')?></span><div><strong>2 materiais de formulários e 1 guia</strong><p>Controles, termo de ciência da equipe e roteiro inicial de implantação.</p></div></article></div></div><p class="disclosure">Os materiais podem ser consultados e personalizados na plataforma, com versões salvas, exportação para Word (DOCX) e opção de impressão em PDF. A revisão e aprovação pelo responsável técnico não estão incluídas.</p></div></section>
<section class="space pack-gallery" aria-label="O que o kit entrega na plataforma">
  <div class="wrap">
    <div class="section-heading centered">
      <span class="sec-label">Materiais organizados em um só lugar</span>
      <h2>Veja como funciona e <em>o que você recebe.</em></h2>
      <p>Uma visão prática do sistema e dos materiais disponíveis no seu acesso.</p>
    </div>
    <div class="pack-gallery-layout">
      <figure class="pack-gallery-card">
        <img src="assets/como-funciona.webp" width="980" height="980" loading="lazy" decoding="async" alt="Fluxo ilustrado: cadastro, personalização dos documentos e acompanhamento">
        <figcaption><strong>Passo a passo guiado</strong><span>Cadastre o consultório, personalize modelos e acompanhe pendências.</span></figcaption>
      </figure>
      <figure class="pack-gallery-card">
        <img src="assets/materiais-plataforma.webp" width="980" height="980" loading="lazy" decoding="async" alt="Pastas e materiais digitais organizados na biblioteca Odonto em Dia">
        <figcaption><strong>Biblioteca de 30 materiais</strong><span>POPs, planos, formulários, SDBPF e guia em uma biblioteca online.</span></figcaption>
      </figure>
    </div>
    <p class="pack-gallery-note">Mockups ilustrativos. Documentos são minutas para adaptação e revisão do responsável técnico.</p>
  </div>
</section>
<section class="space normative" id="base-legal"><div class="wrap"><div class="section-heading split"><div><span class="sec-label">Referências normativas</span><h2>Referências normativas <em>para consulta direta.</em></h2></div><p>A plataforma usa documentos de referência para apoiar a organização. Não é um produto da Anvisa nem possui certificação ou chancela de órgãos públicos.</p></div><div class="norm-grid"><a href="https://anvisalegis.datalegis.net/action/ActionDatalegis.php?acao=abrirTextoAto&amp;cod_menu=8499&amp;cod_modulo=293&amp;link=S&amp;numeroAto=00001002&amp;orgao=RDC%2FDC%2FANVISA%2FMS&amp;seqAto=000&amp;tipo=RDC&amp;valorAno=2025" target="_blank" rel="noopener noreferrer" class="norm-card"><span class="icon-box"><?=si('file')?></span><strong>RDC Anvisa 1.002/2025</strong><small>Boas práticas de funcionamento dos serviços odontológicos</small><span class="norm-foot">Consultar texto da norma <?=si('arrow')?></span></a><a href="https://www.gov.br/anvisa/pt-br/assuntos/servicosdesaude/projeto-de-melhoria-do-processo-de-inspecao-sanitaria-em-servicos-de-saude-e-de-interesse-para-a-saude/harmonizacao-de-roteiros-objetivos-de-inspecao-roi/confira-os-materiais-disponibilizados-roi-planilhas-sintese-e-links-de-limesurvey" target="_blank" rel="noopener noreferrer" class="norm-card"><span class="icon-box"><?=si('clipboard')?></span><strong>Roteiro de Inspeção Odontológica</strong><small>Referência pública da Anvisa, versão 1.2, agosto/2026</small><span class="norm-foot">Consultar material oficial <?=si('arrow')?></span></a><div class="norm-note"><?=si('shield')?><span><b>Importante:</b> “Alinhado à norma” não significa “certificado pela Anvisa”. A implementação e a avaliação da conformidade são atividades distintas da entrega de modelos.</span></div></div></div></section>
<section class="space trust-gallery" aria-label="Exemplos de conversas sobre a experiência">
<div class="wrap"><div class="section-heading centered"><span class="sec-label">Exemplos de mensagens</span><h2>Uma forma simples de <em>organizar a rotina.</em></h2><p>Conversas ilustrativas criadas como simulações, não avaliações recebidas de compradores.</p></div>
<div class="trust-gallery-grid">
<figure class="trust-gallery-card"><img src="/uploads/depoimento1.webp" loading="lazy" decoding="async" alt="Primeira conversa ilustrativa de WhatsApp sobre o kit odontológico"></figure>
<figure class="trust-gallery-card"><img src="/uploads/depoimento2.webp" loading="lazy" decoding="async" alt="Segunda conversa ilustrativa de WhatsApp sobre organização documental"></figure>
<figure class="trust-gallery-card"><img src="/uploads/depoimento3.webp" loading="lazy" decoding="async" alt="Terceira conversa ilustrativa de WhatsApp sobre o kit digital"></figure>
</div></div></section>
<section class="space included"><div class="wrap"><div class="section-heading centered"><span class="sec-label">Sem letras miúdas</span><h2>Saiba exatamente o que entra nos R$ 67.</h2></div><div class="included-grid"><article class="included-card yes"><h3><?=si('check')?> Faz parte do kit principal</h3><ul><li>Plataforma online com login</li><li>Onboarding de configuração da clínica</li><li>Painel de 17 frentes documentais</li><li>Biblioteca de 30 materiais de referência</li><li>Editor assistido, histórico de versões e prévia documental premium</li><li>Autoavaliação inicial de 15 pontos</li><li>Registro de responsáveis, prazos e observações</li></ul></article><article class="included-card no"><h3><?=si('close')?> Vendido à parte ou não fornecido</h3><ul><li>Autoinspeção 360 ampliada: produto complementar</li><li>Consultoria técnica individualizada</li><li>Documentos assinados ou aprovados pelo RT</li><li>Projetos arquitetônicos, laudos e licenças</li><li>Certificação, chancela ou selo da Anvisa</li><li>Garantia de aprovação em fiscalização</li><li>Serviços profissionais não descritos no kit</li></ul></article></div></div></section>
<section class="space portrait-story" aria-label="Apresentação visual do kit digital">
  <div class="wrap portrait-story-layout">
    <div class="portrait-story-copy">
      <span class="sec-label">Seu material, sempre à mão</span>
      <h2>Um kit organizado para <em>o dia a dia do consultório.</em></h2>
      <p>Abra os modelos pela plataforma, personalize os dados da clínica e mantenha as versões preparadas para a revisão do responsável técnico.</p>
      <ul class="portrait-story-benefits">
        <li><?=si('check')?> <span>30 materiais de referência organizados por categoria</span></li>
        <li><?=si('check')?> <span>Editor online com salvamento das versões</span></li>
        <li><?=si('check')?> <span>Documentos organizados para revisar com o RT</span></li>
      </ul>
      <a class="btn btn-large" href="#oferta">Ver o kit completo por R$ 67 <?=si('arrow')?></a>
    </div>
    <figure class="portrait-story-visual">
      <img src="assets/dentista-book-kit.webp" width="1122" height="1402" loading="lazy" decoding="async" alt="Imagem ilustrativa de uma profissional com um book representando a biblioteca digital de documentação odontológica">
      <figcaption>Imagem de apresentação do kit digital. Não há envio de material impresso nem endosso profissional.</figcaption>
    </figure>
  </div>
</section>
<section class="offer-section" id="oferta"><div class="wrap"><div class="offer-layout"><div class="offer-copy"><span class="sec-label light-label">Sua área de trabalho documental</span><h2>Kit digital com editor, versões salvas e acompanhamento por R$ 67.</h2><p>Você recebe 30 materiais de referência organizados numa plataforma onde pode editar, salvar versões, acompanhar pendências e retomar o trabalho no mesmo acesso. Sem pagar por um sistema separado. A revisão técnica continua sob responsabilidade do RT.</p><ul><li><?=si('check')?> 30 materiais de referência</li><li><?=si('check')?> Editor online, histórico de versões e painel de pendências</li><li><?=si('check')?> Pasta documental A4 organizada para imprimir após revisão</li><li><?=si('check')?> Compra única, com 12 meses de acesso online</li></ul></div><div class="pricing"><div class="pricing-top"><span>ODONTO EM DIA</span><span class="price-label">KIT PRINCIPAL</span></div><div class="price-main"><small>R$</small> 67<sup>,00</sup></div><p>Compra única · acesso online por 12 meses.</p><a class="btn btn-large btn-block" href="?page=checkout">Conferir compra e adicionais <?=si('arrow')?></a><div class="pricing-note"><?=si('lock')?> Pagamento seguro pelo Mercado Pago. O acesso é liberado após confirmação do pagamento.</div><div class="pricing-mini">Módulos complementares, como a Autoinspeção 360, não estão incluídos neste valor.</div></div></div></div></section>
<section class="space faq" id="duvidas"><div class="wrap"><div class="section-heading centered"><span class="sec-label">Perguntas frequentes</span><h2>O que saber antes de usar.</h2></div><div class="faq-list"><details><summary>O kit deixa minha clínica regularizada automaticamente?</summary><p>Não. Ele auxilia na organização documental. O responsável técnico deve adaptar, aprovar e implementar os documentos aplicáveis, e a situação do estabelecimento depende da realidade do serviço e das autoridades competentes.</p></details><details><summary>Os documentos já vêm assinados ou certificados?</summary><p>Não. São minutas de referência, não documentos homologados ou certificados oficiais. Não emitimos alvarás, certificações nem laudos.</p></details><details><summary>Os documentos ficam prontos para impressão?</summary><p>Sim. Na plataforma, você seleciona os modelos e monta uma pasta A4 com capa personalizada, índice, divisórias e espaços para assinatura. A prévia abre no navegador, onde você pode imprimir ou salvar como PDF. Os modelos precisam ser adaptados, conferidos e aprovados pelo responsável técnico antes do uso oficial. A plataforma não faz essa aprovação automaticamente.</p></details><details><summary>Quantos materiais estão incluídos?</summary><p>30 materiais na biblioteca: 18 minutas de POPs, 7 roteiros de planos, 2 documentos de SDBPF, 2 materiais de formulários e 1 guia inicial. Também há 17 frentes de acompanhamento no painel e 15 verificações iniciais.</p></details><details><summary>É só uma área de membros com arquivos para baixar?</summary><p>Não. Você acessa uma biblioteca organizada, preenche campos guiados e trabalha nas minutas dentro da plataforma. O sistema registra versões e permite acompanhar pendências. Os documentos continuam sendo modelos de referência que exigem revisão técnica.</p></details><details><summary>Preciso instalar algum programa?</summary><p>Não. O kit fica organizado numa plataforma online que abre no navegador, inclusive no celular. Você abre os modelos, preenche informações guiadas, ajusta o texto quando necessário e salva novas versões. A biblioteca e o acompanhamento ficam no mesmo ambiente.</p></details><details><summary>O valor de R$ 67 inclui a Autoinspeção 360 completa?</summary><p>Não. O kit inclui a autoavaliação inicial com 15 pontos. A Autoinspeção 360 ampliada é um complemento separado e ainda depende de validação antes da comercialização.</p></details><details><summary>Já posso pagar e receber meu acesso?</summary><p>A versão piloto está disponível para demonstração. O checkout ainda está em integração e, por enquanto, a página não aceita pagamentos.</p></details></div></div></section>
</main>
<footer class="site-footer"><div class="wrap footer-flex"><div><a href="?page=inicio" class="footer-brand"><img class="footer-mark" src="assets/favicon.svg" width="26" height="26" alt=""> odonto<span>em dia</span></a><p>Ferramenta auxiliar de organização documental odontológica.<br>Sem vínculo, endosso ou certificação da Anvisa, CRO ou órgãos fiscalizadores.</p></div><div class="footer-links"><a href="#entrega">Ver materiais</a><a href="?page=entrar">Entrar</a><a href="#base-legal">Base normativa</a><span>© 2026 Odonto em Dia</span></div></div></footer>
<div class="mobile-sticky"><span><small>Kit principal</small><b>R$ 67</b></span><a href="?page=checkout" class="btn">Conferir compra <?=si('arrow')?></a></div>
<script src="assets/hero-motion-v1.js?v=20261108" defer></script>
</body>
</html>