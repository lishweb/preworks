(function(){
  var h=document.getElementById('hdr'),b=document.getElementById('burger');
  if(b)b.addEventListener('click',function(){h.classList.toggle('open');});
  document.querySelectorAll('.hdr nav a').forEach(function(a){a.addEventListener('click',function(){h.classList.remove('open');});});
})();
// reveal on scroll（スクロール監視・CSSトランジション・確実発火）
(function(){
  var els=[].slice.call(document.querySelectorAll('.reveal'));
  function check(){
    var vh=window.innerHeight||document.documentElement.clientHeight;
    els=els.filter(function(e){
      var r=e.getBoundingClientRect();
      if(r.top < vh*0.9 && r.bottom > -50){ e.classList.add('in'); return false; }
      return true;
    });
  }
  check();
  window.addEventListener('scroll',check,{passive:true});
  window.addEventListener('resize',check);
  window.addEventListener('load',function(){check();setTimeout(check,200);});
})();
window.addEventListener('load',function(){
  if(!window.gsap||!window.ScrollTrigger){return;}
  gsap.registerPlugin(ScrollTrigger);
  var reduce=window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if(!reduce){
    gsap.from('.hero-copy .super',{y:16,opacity:0,duration:.8,ease:'power3.out',delay:.1});
    gsap.from('.hero-copy h1',{y:32,opacity:0,duration:1,ease:'power3.out',delay:.22});
    gsap.from('.hero-copy .sub',{y:22,opacity:0,duration:.9,ease:'power3.out',delay:.42});
    gsap.from('.hero-proof,.hero-cta,.hero .micro',{y:18,opacity:0,duration:.8,stagger:.1,ease:'power3.out',delay:.58});
    gsap.from('.hero-fig .photo',{scale:.92,opacity:0,duration:1.1,ease:'power3.out',delay:.35});
    gsap.from('.hero-fig .chip',{scale:.6,opacity:0,duration:.7,stagger:.15,ease:'back.out(1.6)',delay:.9});
  } else {
    gsap.set(['.hero-copy .super','.hero-copy h1','.hero-copy .sub','.hero-proof','.hero-cta','.hero .micro','.hero-fig .photo','.hero-fig .chip'],{opacity:1});
  }
  gsap.utils.toArray('.count').forEach(function(el){
    var to=+el.dataset.to,o={v:0};
    gsap.to(o,{v:to,duration:1.8,ease:'power2.out',scrollTrigger:{trigger:el,start:'top 92%',once:true},
      onUpdate:function(){el.textContent=Math.round(o.v).toLocaleString();}});
  });
});

(function(){
  var ph="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='80' height='80'%3E%3Cdefs%3E%3ClinearGradient id='g' x1='0' y1='0' x2='1' y2='1'%3E%3Cstop offset='0' stop-color='%23F8DCC6'/%3E%3Cstop offset='1' stop-color='%23E3A586'/%3E%3C/linearGradient%3E%3C/defs%3E%3Crect width='80' height='80' fill='url(%23g)'/%3E%3C/svg%3E";
  function fix(im){ if(im.dataset.ph)return; im.dataset.ph=1; im.src=ph; }
  document.querySelectorAll('img').forEach(function(im){
    if(im.classList.contains('liico'))return;
    im.addEventListener('error',function(){fix(im);});
    if(im.complete && im.naturalWidth===0) fix(im);
  });
})();
// クライアントロゴ：JS(rAF)で確実にスクロール（CSSアニメの端末依存を回避）
(function(){
  var tracks=document.querySelectorAll('.clients .logo-track');
  if(!tracks.length) return;
  tracks.forEach(function(tr){
    tr.style.animation='none';          // CSSアニメを無効化しJSで駆動
    var reverse=tr.classList.contains('row2');
    var speed=reverse?18:22;            // px/秒
    var half=0, x=0, last=null, paused=false;
    function measure(){ half=tr.scrollWidth/2; if(reverse && x===0) x=-half; }
    if(tr.querySelector('img') && !tr.querySelector('img').complete){
      tr.querySelectorAll('img').forEach(function(im){im.addEventListener('load',measure);});
    }
    measure(); window.addEventListener('resize',measure); window.addEventListener('load',measure);
    tr.addEventListener('mouseenter',function(){paused=true;});
    tr.addEventListener('mouseleave',function(){paused=false;});
    function step(ts){
      if(last===null) last=ts;
      var dt=Math.min(ts-last,64); last=ts;
      if(!paused && half>0){
        if(reverse){ x+=speed*dt/1000; if(x>=0) x-=half; }
        else { x-=speed*dt/1000; if(x<=-half) x+=half; }
        tr.style.transform='translateX('+x.toFixed(2)+'px)';
      }
      requestAnimationFrame(step);
    }
    requestAnimationFrame(step);
  });
})();
/* 1.3.40 もくじの開閉（項目が多いときのみ。JSが動かない環境では全表示のまま） */
(function () {
  document.querySelectorAll('.post-toc[data-collapsible]').forEach(function (nav) {
    var body = nav.querySelector('.toc-body');
    var more = nav.querySelector('.toc-more');
    var btn  = nav.querySelector('.toc-toggle');
    if (!body || !btn || !more) return;
    if (body.scrollHeight <= 260) return;            // 短ければ折りたたまない
    nav.classList.add('is-collapsible');
    more.hidden = false;
    var label = btn.querySelector('.toc-toggle-t');
    btn.addEventListener('click', function () {
      var open = body.classList.toggle('is-open');
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
      btn.setAttribute('aria-label', open ? 'もくじを閉じる' : 'もくじをすべて見る');
      if (label) label.textContent = open ? '閉じる' : '開く';
      if (!open) nav.scrollIntoView({ block: 'nearest' });
    });
  });
})();

/* 1.3.48 表の横スクロールヒント（実際にはみ出している時だけ出す） */
(function () {
  var figs = document.querySelectorAll('.post-body figure.post-table');
  if (!figs.length) return;
  function sync() {
    figs.forEach(function (fig) {
      var sc = fig.querySelector('.table-scroll');
      var hint = fig.querySelector('.table-hint');
      if (!sc || !hint) return;
      /* 数pxのはみ出しでスワイプを促すのは過剰なので、16px以上のときだけ出す */
      hint.hidden = (sc.scrollWidth <= sc.clientWidth + 16);
    });
  }
  sync();
  window.addEventListener('load', sync);
  window.addEventListener('resize', sync);
})();
