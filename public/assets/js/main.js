/* ==========================================================================
   Nexus Invoicing — site script
   Sections: 1) global 3D network background  2) hero 3D scene
             3) CSS tilt  4) nav/scroll/reveal  5) FAQ  6) register form
             7) theme toggle (drives 1 & 2 via exposed setTheme functions)
   ========================================================================== */

function isDarkNow(){
  return document.documentElement.classList.contains('dark');
}

/* ============ 1) GLOBAL 3D NETWORK BACKGROUND (whole page, both themes) ============ */
const NexusBg = (function(){
  const canvas = document.getElementById('bg-canvas');
  const scene = new THREE.Scene();
  let W = window.innerWidth, H = window.innerHeight;
  const camera = new THREE.PerspectiveCamera(55, W/H, 0.1, 100);
  camera.position.set(0, 0, 16);

  const renderer = new THREE.WebGLRenderer({canvas: canvas, antialias:true, alpha:true});
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
  renderer.setSize(W, H);

  const NODE_COUNT = 70;
  const nodes = [];
  const spread = { x: 22, y: 13, z: 8 };
  const nodeGeo = new THREE.SphereGeometry(0.05, 8, 8);
  const blueColor = new THREE.Color(0x1B6FB0);
  const greenColor = new THREE.Color(0x3CAA50);

  const nodeGroup = new THREE.Group();
  scene.add(nodeGroup);

  for(let i=0;i<NODE_COUNT;i++){
    const mixT = Math.random();
    const col = blueColor.clone().lerp(greenColor, mixT);
    const mat = new THREE.MeshBasicMaterial({color:col, transparent:true, opacity:0.55});
    const mesh = new THREE.Mesh(nodeGeo, mat);
    mesh.position.set(
      (Math.random()-0.5)*spread.x*2,
      (Math.random()-0.5)*spread.y*2,
      (Math.random()-0.5)*spread.z*2
    );
    mesh.userData.basePos = mesh.position.clone();
    mesh.userData.driftSeed = Math.random()*Math.PI*2;
    nodeGroup.add(mesh);
    nodes.push(mesh);
  }

  // Line color/opacity are theme-tunable via setTheme() below — this is the
  // one visual element that needs the biggest swing between themes: the
  // same faint teal reads as near-invisible on white and as a glowing wire
  // on near-black, so light/dark get different base opacities.
  const lineMat = new THREE.LineBasicMaterial({color:0x2E8E8E, transparent:true, opacity:0.12});
  const lineGeom = new THREE.BufferGeometry();
  const maxDist = 6.2;

  function rebuildLines(){
    const linePositions = [];
    for(let i=0;i<nodes.length;i++){
      for(let j=i+1;j<nodes.length;j++){
        const d = nodes[i].position.distanceTo(nodes[j].position);
        if(d < maxDist){
          linePositions.push(nodes[i].position.x, nodes[i].position.y, nodes[i].position.z);
          linePositions.push(nodes[j].position.x, nodes[j].position.y, nodes[j].position.z);
        }
      }
    }
    lineGeom.setAttribute('position', new THREE.Float32BufferAttribute(linePositions, 3));
  }
  rebuildLines();
  const lines = new THREE.LineSegments(lineGeom, lineMat);
  scene.add(lines);

  let mouseX = 0, mouseY = 0, targetRotX = 0, targetRotY = 0;
  window.addEventListener('mousemove', (e)=>{
    mouseX = (e.clientX / window.innerWidth) - 0.5;
    mouseY = (e.clientY / window.innerHeight) - 0.5;
  });

  let scrollFrac = 0;
  function onScroll(){
    const max = document.body.scrollHeight - window.innerHeight;
    scrollFrac = max > 0 ? window.scrollY / max : 0;
  }
  window.addEventListener('scroll', onScroll, {passive:true});

  let t = 0;
  let frame = 0;
  function animate(){
    requestAnimationFrame(animate);
    t += 0.004;
    frame++;

    nodes.forEach(n=>{
      const bp = n.userData.basePos;
      n.position.x = bp.x + Math.sin(t*0.6 + n.userData.driftSeed) * 0.35;
      n.position.y = bp.y + Math.cos(t*0.5 + n.userData.driftSeed) * 0.35;
    });

    if(frame % 4 === 0) rebuildLines();

    targetRotY += (mouseX*0.25 - targetRotY) * 0.02;
    targetRotX += (-mouseY*0.15 - targetRotX) * 0.02;
    nodeGroup.rotation.y = targetRotY + scrollFrac * 0.9;
    nodeGroup.rotation.x = targetRotX;
    lines.rotation.copy(nodeGroup.rotation);

    camera.position.y = -scrollFrac * 4;

    renderer.render(scene, camera);
  }
  animate();

  window.addEventListener('resize', ()=>{
    W = window.innerWidth; H = window.innerHeight;
    camera.aspect = W/H; camera.updateProjectionMatrix();
    renderer.setSize(W, H);
  });

  function setTheme(dark){
    const nodeOpacity = dark ? 0.8 : 0.55;
    const lineOpacity = dark ? 0.22 : 0.12;
    nodes.forEach(n => { n.material.opacity = nodeOpacity; });
    lineMat.opacity = lineOpacity;
  }

  return { setTheme };
})();

/* ============ 2) HERO 3D SCENE (both themes) ============ */
const NexusHero = (function(){
  const canvas = document.getElementById('hero-canvas');
  const stage = canvas.parentElement;
  let W = stage.clientWidth, H = stage.clientHeight;

  const scene = new THREE.Scene();
  const camera = new THREE.PerspectiveCamera(38, W/H, 0.1, 100);
  camera.position.set(0, 1.4, 9);

  const renderer = new THREE.WebGLRenderer({canvas: canvas, antialias:true, alpha:true});
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
  renderer.setSize(W, H);

  const ambient = new THREE.AmbientLight(0xffffff, 0.75);
  scene.add(ambient);
  const key = new THREE.DirectionalLight(0x1B6FB0, 1.0);
  key.position.set(4, 6, 5);
  scene.add(key);
  const rim = new THREE.DirectionalLight(0x3CAA50, 0.9);
  rim.position.set(-5, -2, -4);
  scene.add(rim);

  const group = new THREE.Group();
  scene.add(group);

  // Real invoice and receipt images
  const textureLoader = new THREE.TextureLoader();
  const invoiceGroup = new THREE.Group();
  
  // Load invoice and receipt images
  const invoiceTexture = textureLoader.load('/assets/img/invoice-sample.png');
  const receiptTexture = textureLoader.load('/assets/img/receipt-sample.png');
  
  const invoiceGeo = new THREE.PlaneGeometry(3.2, 4.0);
  
  // Invoice material
  const invoiceMat = new THREE.MeshBasicMaterial({ 
    map: invoiceTexture,
    side: THREE.DoubleSide,
    transparent: true
  });
  const invoice = new THREE.Mesh(invoiceGeo, invoiceMat);
  invoiceGroup.add(invoice);

  // Receipt material (initially hidden)
  const receiptMat = new THREE.MeshBasicMaterial({ 
    map: receiptTexture,
    side: THREE.DoubleSide,
    transparent: true,
    opacity: 0
  });
  const receipt = new THREE.Mesh(invoiceGeo, receiptMat);
  receipt.position.z = 0.01;
  invoiceGroup.add(receipt);

  // Add subtle shadow/backing
  const backingGeo = new THREE.PlaneGeometry(3.3, 4.1);
  const backingMat = new THREE.MeshBasicMaterial({ 
    color: 0x000000, 
    transparent: true, 
    opacity: 0.1,
    side: THREE.DoubleSide
  });
  const backing = new THREE.Mesh(backingGeo, backingMat);
  backing.position.z = -0.01;
  invoiceGroup.add(backing);

  // Position the invoice
  invoiceGroup.position.set(-1.5, 1.2, 0);
  invoiceGroup.rotation.y = 0.2;
  group.add(invoiceGroup);
  
  const cards = [invoice];

  // Dollar signs rain system
  const dollarNotes = [];
  const dollarNoteGroup = new THREE.Group();
  group.add(dollarNoteGroup);

  // Create dollar sign texture using canvas
  function createDollarTexture() {
    const canvas = document.createElement('canvas');
    canvas.width = 128;
    canvas.height = 128;
    const ctx = canvas.getContext('2d');
    
    // Draw dollar sign
    ctx.fillStyle = '#3CAA50';
    ctx.font = 'bold 100px Arial';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText('$', 64, 64);
    
    const texture = new THREE.CanvasTexture(canvas);
    return texture;
  }

  const dollarTexture = createDollarTexture();
  const dollarGeo = new THREE.PlaneGeometry(0.4, 0.4);
  const dollarMat = new THREE.MeshBasicMaterial({
    map: dollarTexture,
    side: THREE.DoubleSide,
    transparent: true
  });

  function createDollarNote() {
    const note = new THREE.Mesh(dollarGeo, dollarMat.clone());
    note.position.set(
      (Math.random() - 0.5) * 4,
      3 + Math.random() * 2,
      (Math.random() - 0.5) * 2
    );
    note.rotation.x = Math.random() * Math.PI;
    note.rotation.y = Math.random() * Math.PI;
    note.rotation.z = Math.random() * Math.PI;
    note.userData = {
      velocity: 0.02 + Math.random() * 0.03,
      rotationSpeed: 0.02 + Math.random() * 0.05,
      wobble: Math.random() * Math.PI * 2
    };
    return note;
  }

  let rainActive = false;
  let lastRainTime = 0;

  // coin
  const coinGeo = new THREE.CylinderGeometry(0.55, 0.55, 0.14, 48);
  const coinMat = new THREE.MeshStandardMaterial({color:0x1B6FB0, roughness:0.3, metalness:0.6});
  const coin = new THREE.Mesh(coinGeo, coinMat);
  coin.rotation.x = Math.PI/2.4;
  coin.position.set(1.9, 1.6, 0.6);
  group.add(coin);

  const coinEdge = new THREE.Mesh(
    new THREE.TorusGeometry(0.55, 0.02, 8, 48),
    new THREE.MeshStandardMaterial({color:0x3CAA50, roughness:0.35, metalness:0.6})
  );
  coin.add(coinEdge);

  // wallet / settlement block
  const walletGeo = new THREE.BoxGeometry(1.7, 1.05, 1.0);
  const walletMat = new THREE.MeshStandardMaterial({color:0x113F6E, roughness:0.6, metalness:0.15});
  const wallet = new THREE.Mesh(walletGeo, walletMat);
  wallet.position.set(2.1, -1.35, 0.3);
  group.add(wallet);
  const walletSlot = new THREE.Mesh(
    new THREE.BoxGeometry(1.1, 0.08, 1.02),
    new THREE.MeshStandardMaterial({color:0x3CAA50, roughness:0.35})
  );
  walletSlot.position.set(2.1, -0.85, 0.3);
  group.add(walletSlot);

  // orbit rings echoing the logo's swoosh
  const ringMat = new THREE.MeshBasicMaterial({color:0x3CAA50, transparent:true, opacity:0.5});
  const ring = new THREE.Mesh(new THREE.TorusGeometry(2.5, 0.02, 8, 100, Math.PI*1.5), ringMat);
  ring.rotation.x = Math.PI/2.1;
  ring.position.y = -0.2;
  scene.add(ring);

  const ring2Mat = new THREE.MeshBasicMaterial({color:0x1B6FB0, transparent:true, opacity:0.4});
  const ring2 = new THREE.Mesh(new THREE.TorusGeometry(2.5, 0.014, 8, 100, Math.PI*1.1), ring2Mat);
  ring2.rotation.x = Math.PI/2.1;
  ring2.rotation.z = Math.PI;
  ring2.position.y = -0.2;
  scene.add(ring2);

  group.rotation.y = -0.35;
  group.position.x = 1.5;

  let dragging = false, lastX = 0, lastY = 0;
  let targetRotY = group.rotation.y, targetRotX = 0.05;
  let autorotate = false;

  canvas.addEventListener('pointerdown', (e)=>{
    dragging = true; lastX = e.clientX; lastY = e.clientY;
    canvas.style.cursor = 'grabbing';
    autorotate = false;
  });
  window.addEventListener('pointerup', ()=>{ dragging = false; canvas.style.cursor = 'grab'; });
  window.addEventListener('pointermove', (e)=>{
    if(!dragging) return;
    const dx = e.clientX - lastX, dy = e.clientY - lastY;
    lastX = e.clientX; lastY = e.clientY;
    targetRotY += dx * 0.005;
    targetRotX = Math.max(-0.3, Math.min(0.5, targetRotX + dy*0.003));
  });

  // Hover-based rotation and rain effect
  canvas.addEventListener('mouseenter', ()=>{
    if(!dragging) autorotate = true;
    rainActive = true;
  });
  canvas.addEventListener('mouseleave', ()=>{
    autorotate = false;
    rainActive = false;
    // Clear existing dollar notes
    dollarNotes.forEach(note => dollarNoteGroup.remove(note));
    dollarNotes.length = 0;
  });

  let t = 0;
  function animate(){
    requestAnimationFrame(animate);
    t += 0.01;

    if(autorotate){ targetRotY += 0.0022; }
    group.rotation.y += (targetRotY - group.rotation.y) * 0.08;
    group.rotation.x += (targetRotX - group.rotation.x) * 0.08;

    coin.rotation.z += 0.015;
    coin.position.y = 1.6 + Math.sin(t*1.3) * 0.12;

    // Subtle floating animation for the invoice
    invoiceGroup.position.y = 1.2 + Math.sin(t*0.6) * 0.05;

    // Toggle between invoice and receipt based on rotation
    const rotationPhase = (group.rotation.y % (Math.PI * 2));
    if (rotationPhase > Math.PI) {
      // Show receipt
      invoiceMat.opacity = 0;
      receiptMat.opacity = 1;
    } else {
      // Show invoice
      invoiceMat.opacity = 1;
      receiptMat.opacity = 0;
    }

    // Dollar notes rain effect
    if (rainActive) {
      // Add new notes periodically
      if (t - lastRainTime > 0.1 && dollarNotes.length < 30) {
        const note = createDollarNote();
        dollarNotes.push(note);
        dollarNoteGroup.add(note);
        lastRainTime = t;
      }

      // Update existing notes
      for (let i = dollarNotes.length - 1; i >= 0; i--) {
        const note = dollarNotes[i];
        note.position.y -= note.userData.velocity;
        note.rotation.x += note.userData.rotationSpeed;
        note.rotation.y += note.userData.rotationSpeed;
        note.position.x += Math.sin(t * 2 + note.userData.wobble) * 0.01;

        // Remove notes that fall below view
        if (note.position.y < -3) {
          dollarNoteGroup.remove(note);
          dollarNotes.splice(i, 1);
        }
      }
    }

    ring.rotation.z += 0.0018;
    ring2.rotation.z -= 0.0012;

    renderer.render(scene, camera);
  }
  animate();

  function onResize(){
    W = stage.clientWidth; H = stage.clientHeight;
    camera.aspect = W/H;
    camera.updateProjectionMatrix();
    renderer.setSize(W, H);
  }
  window.addEventListener('resize', onResize);
  onResize();

  function setTheme(dark){
    // Update wallet color for dark mode
    const walletColor = dark ? 0x24507F : 0x113F6E;
    wallet.material.color.setHex(walletColor);

    // Dim the flat ambient wash and let the blue/green directional lights
    // carry more of the scene — reads as moodier and more dimensional
    // rather than just "the same lighting, darker background."
    ambient.intensity = dark ? 0.45 : 0.75;
    ambient.color.setHex(dark ? 0xAAB4C0 : 0xffffff);

    ringMat.opacity = dark ? 0.65 : 0.5;
    ring2Mat.opacity = dark ? 0.55 : 0.4;
  }

  return { setTheme };
})();

/* ============ 3) CSS 3D TILT for feature cards ============ */
document.querySelectorAll('.tilt').forEach(el=>{
  el.addEventListener('mousemove', (e)=>{
    const r = el.getBoundingClientRect();
    const px = (e.clientX - r.left) / r.width - 0.5;
    const py = (e.clientY - r.top) / r.height - 0.5;
    el.style.transform = `perspective(700px) rotateX(${py*-7}deg) rotateY(${px*7}deg) translateZ(6px)`;
  });
  el.addEventListener('mouseleave', ()=>{ el.style.transform = 'perspective(700px) rotateX(0) rotateY(0)'; });
});

/* ============ 4) NAV SCROLL STATE + SCROLL-TO-TOP + SCROLL REVEAL ============ */
const navEl = document.getElementById('nav');
const scrollTopBtn = document.getElementById('scroll-top');
window.addEventListener('scroll', ()=>{
  const scrolled = window.scrollY > 40;
  navEl.classList.toggle('scrolled', scrolled);
  if(scrollTopBtn) scrollTopBtn.classList.toggle('visible', window.scrollY > 400);
}, {passive:true});

if(scrollTopBtn){
  scrollTopBtn.addEventListener('click', (e)=>{
    e.preventDefault();
    window.scrollTo({top:0, behavior:'smooth'});
  });
}

const revealEls = document.querySelectorAll('.reveal');
const io = new IntersectionObserver((entries)=>{
  entries.forEach(e=>{
    if(e.isIntersecting){ e.target.classList.add('in'); io.unobserve(e.target); }
  });
}, {threshold:0.12});
revealEls.forEach(el=>io.observe(el));

/* ============ 5) FAQ ACCORDION ============ */
document.querySelectorAll('.faq-item').forEach(item=>{
  const question = item.querySelector('.faq-question');
  question.addEventListener('click', ()=>{
    item.classList.toggle('open');
  });
});

/* ============ 6) REGISTER FORM ============ */
const registerForm = document.getElementById('register-form');
if(registerForm){
  registerForm.addEventListener('submit', (e)=>{
    e.preventDefault();
    const btn = registerForm.querySelector('.btn-primary');
    btn.textContent = 'Request received ✓';
    btn.disabled = true;
  });
}

/* ============ 7) THEME TOGGLE ============
   The *first* paint decision (avoiding a flash of the wrong theme) happens in
   a small inline script in app.blade.php's <head>, since it has to run before
   nexus.css loads. This block wires up the button, keeps localStorage in
   sync, and — the part that matters for the 3D — pushes the theme into both
   Three.js scenes via NexusBg.setTheme()/NexusHero.setTheme() so the network
   background and hero scene retint live instead of only on reload. */
(function(){
  const KEY = 'nexus-theme';
  const root = document.documentElement;
  const btn = document.getElementById('theme-toggle');

  function paintButton(theme){
    if(!btn) return;
    btn.setAttribute('aria-label', theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode');
    btn.innerHTML = theme === 'dark'
      ? '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>'
      : '<svg viewBox="0 0 24 24"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>';
  }

  function applyTheme(dark){
    root.classList.toggle('dark', dark);
    paintButton(dark ? 'dark' : 'light');
    NexusBg.setTheme(dark);
    NexusHero.setTheme(dark);
  }

  // Sync everything (button + both 3D scenes) to whatever the inline head
  // script already applied to the <html> class before this file loaded.
  applyTheme(isDarkNow());

  if(btn){
    btn.addEventListener('click', ()=>{
      const next = !isDarkNow();
      applyTheme(next);
      localStorage.setItem(KEY, next ? 'dark' : 'light');
    });
  }

  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e)=>{
    if(!localStorage.getItem(KEY)){
      applyTheme(e.matches);
    }
  });
})();