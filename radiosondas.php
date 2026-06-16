<?php
require_once 'nav.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>📡 Panel de Radiosondas · RadioTools</title>
<link rel="stylesheet" href="styles.css">
<style>
  :root{
    --rs-bg:#0f1720; --rs-panel:#16212e; --rs-panel2:#1c2a3a; --rs-line:#2b3c4f;
    --rs-txt:#e6edf3; --rs-muted:#8aa0b4; --rs-accent:#3da9fc; --rs-ok:#37d67a;
    --rs-warn:#f5a623; --rs-bad:#e8576b;
  }
  .rs-wrap{max-width:1500px;margin:0 auto;color:var(--rs-txt)}
  .rs-header{padding:14px 16px;background:linear-gradient(90deg,#13202e,#0f1720);border:1px solid var(--rs-line);border-radius:10px;margin-bottom:16px}
  .rs-header h2{margin:0;font-size:18px;letter-spacing:.3px;color:var(--rs-txt)}
  .rs-header small{color:var(--rs-muted);font-weight:400;font-size:12px;margin-left:8px}
  .rs-controls{display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;background:var(--rs-panel);border:1px solid var(--rs-line);border-radius:10px;padding:14px}
  .rs-fld{display:flex;flex-direction:column;gap:4px}
  .rs-fld label{font-size:11px;color:var(--rs-muted);text-transform:uppercase;letter-spacing:.5px}
  .rs-wrap input,.rs-wrap select{background:var(--rs-panel2);border:1px solid var(--rs-line);color:var(--rs-txt);border-radius:7px;padding:7px 9px;font-size:14px}
  .rs-wrap input[type=number]{width:120px}
  .rs-wrap button{cursor:pointer;border:1px solid var(--rs-line);background:var(--rs-panel2);color:var(--rs-txt);border-radius:7px;padding:8px 12px;font-size:13px;transition:.15s}
  .rs-wrap button:hover{border-color:var(--rs-accent)}
  .rs-wrap button.rs-primary{background:var(--rs-accent);border-color:var(--rs-accent);color:#06121d;font-weight:600}
  .rs-wrap button.rs-preset{background:#1a2c3d}
  .rs-presets{display:flex;gap:8px;flex-wrap:wrap}
  .rs-opt{display:flex;align-items:center;gap:6px;color:var(--rs-muted);font-size:13px}
  #rs-status{margin:14px 0;color:var(--rs-muted);min-height:20px}
  #rs-summary{display:flex;gap:18px;flex-wrap:wrap;margin:8px 0 14px}
  #rs-summary .rs-card{background:var(--rs-panel);border:1px solid var(--rs-line);border-radius:10px;padding:10px 16px}
  #rs-summary .rs-card b{font-size:22px;display:block;color:var(--rs-txt)}
  #rs-summary .rs-card span{color:var(--rs-muted);font-size:12px}
  .rs-tablewrap{overflow-x:auto}
  .rs-tablewrap table{width:100%;border-collapse:collapse;background:var(--rs-panel);border:1px solid var(--rs-line);border-radius:10px;overflow:hidden;font-size:13px}
  .rs-tablewrap th,.rs-tablewrap td{padding:8px 10px;text-align:left;border-bottom:1px solid var(--rs-line);white-space:nowrap}
  .rs-tablewrap th{background:#13202e;cursor:pointer;user-select:none;font-size:12px;text-transform:uppercase;letter-spacing:.4px;color:var(--rs-muted);position:sticky;top:0}
  .rs-tablewrap th:hover{color:var(--rs-txt)}
  .rs-tablewrap th .rs-arrow{opacity:.5;font-size:10px}
  .rs-tablewrap tr:hover td{background:rgba(24,38,53,0.5)}
  .rs-tablewrap td a{color:var(--rs-accent);text-decoration:none}
  .rs-tablewrap td a:hover{text-decoration:underline}
  .rs-recYes{opacity:.5}
  .rs-pill{padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600}
  .rs-fix-EXCELENTE{background:#0c3d24;color:var(--rs-ok)}
  .rs-fix-BUENA{background:#123a2a;color:#7be0a8}
  .rs-fix-MEDIA{background:#3d3413;color:var(--rs-warn)}
  .rs-fix-BAJA{background:#3d1722;color:var(--rs-bad)}
  .rs-sea{color:var(--rs-bad);font-size:11px}
  .rs-right{text-align:right}
  .rs-foot{color:var(--rs-muted);font-size:11px;margin-top:14px;line-height:1.6;background:var(--rs-panel);border:1px solid var(--rs-line);border-radius:10px;padding:12px}
  .rs-foot code{background:#0a131c;padding:1px 5px;border-radius:4px;color:var(--rs-txt)}
  @media (max-width:600px){
    .rs-wrap input[type=number]{width:100%}
    .rs-fld{flex:1 1 45%}
  }
</style>
</head>
<body>
<div class="container" style="max-width:1500px;">
  <?php renderNavMenu('radiosondas.php'); ?>

  <div class="rs-wrap">
    <div class="rs-header">
      <h2>📡 Panel de radiosondas <small>vía API pública de SondeHub · recuperación</small></h2>
    </div>

    <div class="rs-controls">
      <div class="rs-fld"><label>Latitud</label><input id="rs-lat" type="number" step="0.000001" value="43.416390"></div>
      <div class="rs-fld"><label>Longitud</label><input id="rs-lon" type="number" step="0.000001" value="-3.847237"></div>
      <div class="rs-fld"><label>Radio (km)</label><input id="rs-radius" type="number" step="5" value="20"></div>
      <div class="rs-fld"><label>Ventana</label>
        <select id="rs-duration">
          <option value="6h">6 h</option>
          <option value="12h">12 h</option>
          <option value="1d" selected>1 día</option>
          <option value="3d">3 días (máx)</option>
        </select>
      </div>
      <div class="rs-fld"><label>Alt. máx. último dato (m)</label><input id="rs-maxalt" type="number" step="500" value="5000"></div>
      <label class="rs-opt"><input id="rs-onlyNotRec" type="checkbox"> Solo NO recuperadas</label>
      <button class="rs-primary" onclick="rsBuscar()">🔍 Buscar</button>
      <div class="rs-fld"><label>Zonas rápidas</label>
        <div class="rs-presets">
          <button class="rs-preset" onclick="rsSetZona(43.416390,-3.847237)">Zona 1</button>
          <button class="rs-preset" onclick="rsSetZona(43.385082,-4.290887)">Zona 2</button>
        </div>
      </div>
    </div>

    <div id="rs-status">Listo. Pulsa <b>Buscar</b> o elige una zona rápida.</div>
    <div id="rs-summary"></div>
    <div id="rs-tablewrap" class="rs-tablewrap"></div>

    <div class="rs-foot">
      <b>Cómo leerlo:</b> la API <code>/sondes/telemetry</code> devuelve sondas de todo el mundo (no filtra por zona) y solo guarda hasta 3 días, así que el filtro de distancia se hace aquí en local.
      El <b>Fix</b> indica lo fiable que es el punto de aterrizaje (cuanto más baja fue la última altura recibida, mejor). La <b>estimación de aterrizaje</b> extrapola el último descenso asumiendo cota de terreno 0 → en montaña sobreestima; busca <i>entre</i> el último punto y la estimación.
      Las sondas que derivan al norte hacia el mar Cantábrico se marcan como posible 🌊.
      <br>Cruza con <code>/recovered</code> (últimos 60 días) para saber cuáles ya tienen dueño.
    </div>
  </div>
</div>

<script>
const RS_API = "https://api.v2.sondehub.org";
let rsRows = [];
let rsSortKey = "dist", rsSortDir = 1;

function rsSetZona(la,lo){ document.getElementById('rs-lat').value=la; document.getElementById('rs-lon').value=lo; rsBuscar(); }

function rsHaversine(la1,lo1,la2,lo2){
  const R=6371, t=Math.PI/180;
  const dLa=(la2-la1)*t, dLo=(lo2-lo1)*t;
  const a=Math.sin(dLa/2)**2+Math.cos(la1*t)*Math.cos(la2*t)*Math.sin(dLo/2)**2;
  return R*2*Math.atan2(Math.sqrt(a),Math.sqrt(1-a));
}
function rsBearing(la1,lo1,la2,lo2){
  const t=Math.PI/180,p1=la1*t,p2=la2*t,dl=(lo2-lo1)*t;
  const y=Math.sin(dl)*Math.cos(p2), x=Math.cos(p1)*Math.sin(p2)-Math.sin(p1)*Math.cos(p2)*Math.cos(dl);
  return (Math.atan2(y,x)*180/Math.PI+360)%360;
}
function rsFixCat(alt){ return alt<300?"EXCELENTE":alt<800?"BUENA":alt<1500?"MEDIA":"BAJA"; }

async function rsBuscar(){
  const lat=+document.getElementById('rs-lat').value, lon=+document.getElementById('rs-lon').value;
  const radius=+document.getElementById('rs-radius').value, dur=document.getElementById('rs-duration').value;
  const maxalt=+document.getElementById('rs-maxalt').value;
  const st=document.getElementById('rs-status');
  st.innerHTML="⏳ Consultando SondeHub… (la ventana de 3 días puede tardar unos segundos)";
  document.getElementById('rs-summary').innerHTML=""; document.getElementById('rs-tablewrap').innerHTML="";
  try{
    const dm=Math.round(radius*1000);
    const [recR,telR]=await Promise.all([
      fetch(`${RS_API}/recovered?lat=${lat}&lon=${lon}&distance=${dm}&last=5184000`).then(r=>r.json()),
      fetch(`${RS_API}/sondes/telemetry?duration=${dur}&lat=${lat}&lon=${lon}&distance=${dm}`).then(r=>r.json())
    ]);
    const recSet={}; (Array.isArray(recR)?recR:[]).forEach(x=>{ if(x.recovered) recSet[x.serial]=x.recovered_by||"sí"; });
    const now=Date.now();
    rsRows=[];
    for(const serial in telR){
      const frames=Object.values(telR[serial]);
      if(!frames.length) continue;
      frames.sort((a,b)=> a.datetime<b.datetime?-1:1);
      const last=frames[frames.length-1];
      if(last.lat==null||last.lon==null) continue;
      const dist=rsHaversine(lat,lon,+last.lat,+last.lon);
      if(dist>radius) continue;
      if(+last.alt>maxalt) continue;
      const isRec=serial in recSet;
      const lastT=new Date(last.datetime+ (last.datetime.endsWith('Z')?'':'Z')).getTime();
      let prev=frames.filter(f=> new Date(f.datetime+(f.datetime.endsWith('Z')?'':'Z')).getTime()<=lastT-60000).pop();
      if(!prev) prev=frames[Math.max(0,frames.length-5)];
      let vv=+last.vel_v; if(!(vv<=-0.5)) vv=-5;
      let vh=+last.vel_h; if(!(vh>0)) vh=8;
      const alt=+last.alt, tg=alt/Math.abs(vv), drift=vh*tg;
      const brg=(prev&&prev.lat!=null)?rsBearing(+prev.lat,+prev.lon,+last.lat,+last.lon):0;
      const eLat=+(last.lat + drift*Math.cos(brg*Math.PI/180)/111320).toFixed(5);
      const eLon=+(last.lon + drift*Math.sin(brg*Math.PI/180)/(111320*Math.cos(lat*Math.PI/180))).toFixed(5);
      const ageH=(now-lastT)/3600000;
      const sea=(+last.lat>43.46 && +last.lon>-4.2 && +last.lon<-1.5);
      rsRows.push({
        serial, tipo:last.type||"?", subtype:last.subtype||"",
        dt:lastT, dtTxt:new Date(lastT).toLocaleString('es-ES',{day:'2-digit',month:'2-digit',hour:'2-digit',minute:'2-digit'}),
        ageH:+ageH.toFixed(1), alt:Math.round(alt), vv:+vv.toFixed(1),
        dist:+dist.toFixed(1), lat:+(+last.lat).toFixed(5), lon:+(+last.lon).toFixed(5),
        eLat, eLon, drift:+(drift/1000).toFixed(1), brg:Math.round(brg),
        rec:isRec, recBy:recSet[serial]||"", fix:rsFixCat(alt), sea
      });
    }
    if(document.getElementById('rs-onlyNotRec').checked) rsRows=rsRows.filter(r=>!r.rec);
    const nRec=Object.keys(recSet).length;
    document.getElementById('rs-summary').innerHTML=
      `<div class="rs-card"><b>${rsRows.length}</b><span>sondas en lista</span></div>`+
      `<div class="rs-card"><b>${rsRows.filter(r=>!r.rec).length}</b><span>sin recuperar</span></div>`+
      `<div class="rs-card"><b>${nRec}</b><span>recuperadas (60d/zona)</span></div>`+
      `<div class="rs-card"><b>${rsRows.filter(r=>r.fix=='EXCELENTE'||r.fix=='BUENA').length}</b><span>con buen fix</span></div>`;
    st.innerHTML = rsRows.length? `✅ ${rsRows.length} resultados en ${radius} km (ventana ${dur}).` : "Sin resultados en esa zona/ventana. Prueba a ampliar radio o ventana.";
    rsSortKey="dist"; rsSortDir=1; rsRender();
  }catch(e){ st.innerHTML="❌ Error: "+e.message+" (¿sin conexión?)"; }
}

const RS_COLS=[
  {k:"serial",t:"Serial"},{k:"tipo",t:"Tipo"},
  {k:"dtTxt",t:"Última (local)",sortk:"dt"},{k:"ageH",t:"Edad (h)",num:1},
  {k:"alt",t:"Últ. alt (m)",num:1},{k:"vv",t:"V. bajada",num:1},
  {k:"dist",t:"Dist (km)",num:1},{k:"fix",t:"Fix"},
  {k:"rec",t:"Recup."},{k:"_maps",t:"Última pos."},{k:"_est",t:"Aterrizaje est."}
];
function rsSetSort(k){ if(rsSortKey===k){rsSortDir*=-1}else{rsSortKey=k;rsSortDir=1} rsRender(); }
function rsRender(){
  const data=[...rsRows].sort((a,b)=>{
    let va=a[rsSortKey], vb=b[rsSortKey];
    if(typeof va==="boolean"){va=va?1:0; vb=vb?1:0;}
    if(va<vb) return -1*rsSortDir; if(va>vb) return 1*rsSortDir; return 0;
  });
  let h="<table><thead><tr>";
  RS_COLS.forEach(c=>{ const sk=c.sortk||c.k; const arr=(rsSortKey===sk)?(rsSortDir>0?"▲":"▼"):"";
    h+=`<th onclick="rsSetSort('${sk}')">${c.t} <span class="rs-arrow">${arr}</span></th>`; });
  h+="</tr></thead><tbody>";
  data.forEach(r=>{
    h+=`<tr class="${r.rec?'rs-recYes':''}">`+
      `<td><a href="https://sondehub.org/${encodeURIComponent(r.serial)}" target="_blank" rel="noopener">${r.serial}</a></td>`+
      `<td>${r.tipo}${r.subtype?'<span style="color:var(--rs-muted)">·'+r.subtype+'</span>':''}</td>`+
      `<td>${r.dtTxt}</td>`+
      `<td class="rs-right">${r.ageH}</td>`+
      `<td class="rs-right">${r.alt.toLocaleString('es-ES')}</td>`+
      `<td class="rs-right">${r.vv}</td>`+
      `<td class="rs-right">${r.dist}</td>`+
      `<td><span class="rs-pill rs-fix-${r.fix}">${r.fix}</span> ${r.sea?'<span class="rs-sea">🌊</span>':''}</td>`+
      `<td>${r.rec?('✔ '+r.recBy):'—'}</td>`+
      `<td><a href="https://www.google.com/maps?q=${r.lat},${r.lon}" target="_blank" rel="noopener">${r.lat},${r.lon}</a></td>`+
      `<td><a href="https://www.google.com/maps?q=${r.eLat},${r.eLon}" target="_blank" rel="noopener">${r.eLat},${r.eLon}</a><br><span style="color:var(--rs-muted);font-size:11px">deriva ~${r.drift}km · rumbo ${r.brg}°</span></td>`+
      `</tr>`;
  });
  h+="</tbody></table>";
  document.getElementById('rs-tablewrap').innerHTML=h;
}
</script>
</body>
</html>
