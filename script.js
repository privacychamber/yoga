// Register Service Worker for PWA
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('sw.js')
      .then(() => console.log('SW Registered'))
      .catch(err => console.log('SW Error', err));
  });
}

/* ===== THEME TOGGLE ===== */
const html = document.documentElement;
const themeToggle = document.getElementById('themeToggle');

// Load saved theme or default to dark
const savedTheme = localStorage.getItem('theme') || 'light';
html.setAttribute('data-theme', savedTheme);

themeToggle.addEventListener('click', () => {
  const current = html.getAttribute('data-theme');
  const next = current === 'dark' ? 'light' : 'dark';
  html.setAttribute('data-theme', next);
  localStorage.setItem('theme', next);
});

/* ===== HAMBURGER MENU ===== */
const hamburger = document.getElementById('hamburger');
const navLinks = document.getElementById('navLinks');

hamburger.addEventListener('click', () => {
  navLinks.classList.toggle('open');
  hamburger.classList.toggle('active');
});

// Close nav on link click (mobile)
navLinks.querySelectorAll('a').forEach(link => {
  link.addEventListener('click', () => {
    navLinks.classList.remove('open');
    hamburger.classList.remove('active');
  });
});

/* ===== NAVBAR SCROLL ===== */
const navbar = document.getElementById('navbar');
window.addEventListener('scroll', () => {
  navbar.classList.toggle('scrolled', window.scrollY > 40);
});

/* ===== PARTICLES ===== */
const particleContainer = document.getElementById('particles');
const PARTICLE_COUNT = 30;

for (let i = 0; i < PARTICLE_COUNT; i++) {
  const p = document.createElement('div');
  p.className = 'particle';
  p.style.left = Math.random() * 100 + '%';
  p.style.animationDelay = Math.random() * 12 + 's';
  p.style.animationDuration = (8 + Math.random() * 10) + 's';
  p.style.width = p.style.height = (1 + Math.random() * 3) + 'px';
  p.style.opacity = (0.2 + Math.random() * 0.5).toString();
  particleContainer.appendChild(p);
}

/* ===== STATS COUNTER ===== */
function animateCounters() {
  document.querySelectorAll('.num[data-to]').forEach(el => {
    const target = parseInt(el.getAttribute('data-to'));
    const duration = 2000;
    const step = target / (duration / 16);
    let current = 0;

    const timer = setInterval(() => {
      current += step;
      if (current >= target) {
        current = target;
        clearInterval(timer);
      }
      el.textContent = Math.floor(current).toLocaleString();
    }, 16);
  });
}

// Trigger counter when stats section is visible
const statsSection = document.getElementById('stats');
let countersStarted = false;
const statsObserver = new IntersectionObserver((entries) => {
  if (entries[0].isIntersecting && !countersStarted) {
    countersStarted = true;
    animateCounters();
  }
}, { threshold: 0.3 });
if (statsSection) statsObserver.observe(statsSection);

/* ===== SCROLL REVEAL ===== */
const revealObserver = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('revealed');
      revealObserver.unobserve(entry.target);
    }
  });
}, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

document.querySelectorAll('.pcard, .qcard, .rcard, .prcard, .fitem, .titem, .lf, .bpoint, .stat').forEach(el => {
  el.classList.add('reveal');
  revealObserver.observe(el);
});

/* ===== HERO SLIDESHOW ===== */
let currentSlide = 0;
const slides = document.querySelectorAll('.hero-slide');
const dots = document.querySelectorAll('#sliderPagination .dot');
let slideInterval = setInterval(nextSlide, 5000);

function showSlide(index) {
  if (!slides.length) return;
  slides.forEach((slide, i) => {
    slide.classList.toggle('active', i === index);
  });
  dots.forEach((dot, i) => {
    dot.classList.toggle('active', i === index);
  });
}

function nextSlide() {
  currentSlide = (currentSlide + 1) % slides.length;
  showSlide(currentSlide);
}

function setSlide(index) {
  currentSlide = index;
  showSlide(currentSlide);
  clearInterval(slideInterval);
  slideInterval = setInterval(nextSlide, 5000);
}

/* ===== DISCOVERY FORM BATCH OPTIONS ===== */
const batches200 = [
  "1st Oct – 24th Oct 2026",
  "1st Nov – 24th Nov 2026",
  "1st Dec – 24th Dec 2026",
  "1st Jan – 24th Jan 2027",
  "1st Feb – 24th Feb 2027",
  "1st Mar – 24th Mar 2027",
  "1st Apr – 24th Apr 2027",
  "1st May – 24th May 2027",
  "1st Jun – 24th Jun 2027",
  "1st Jul – 24th Jul 2027",
  "1st Aug – 24th Aug 2027",
  "1st Sep – 24th Sep 2027",
  "1st Oct – 24th Oct 2027"
];

const batches300 = [
  "1st Apr – 4th May 2027",
  "1st May – 4th Jun 2027",
  "18th Jun – 12th Jul 2027",
  "21st Sep – 25th Oct 2027",
  "22nd Sep – 26th Oct 2027"
];

function updateDiscoveryBatches() {
  const progSelect = document.getElementById('dprog');
  const batchSelect = document.getElementById('dbatch');
  if (!progSelect || !batchSelect) return;
  
  const selectedProg = progSelect.value;
  batchSelect.innerHTML = '';
  
  const list = selectedProg.includes('200-Hour') ? batches200 : batches300;
  list.forEach(batch => {
    const opt = document.createElement('option');
    opt.value = batch;
    opt.textContent = batch;
    batchSelect.appendChild(opt);
  });
}

// Initial populate of batches on load
document.addEventListener('DOMContentLoaded', () => {
  updateDiscoveryBatches();
  loadDynamicContent();
  initReels();
});

/* ===== DYNAMIC CONTENT ===== */
async function loadDynamicContent() {
  try {
    const response = await fetch('content.json?' + new Date().getTime());
    if (!response.ok) return;
    const data = await response.json();
    
    // Update Room Images
    if (data.rooms) {
      if (data.rooms.room1) {
        const room1Img = document.getElementById('room1-img');
        if (room1Img) room1Img.src = data.rooms.room1;
      }
      if (data.rooms.room2) {
        const room2Img = document.getElementById('room2-img');
        if (room2Img) room2Img.src = data.rooms.room2;
      }
    }
    
    // Update Reels from content.json
    if (data.reels && Array.isArray(data.reels) && data.reels.length > 0) {
      data.reels.forEach((r, idx) => {
        if (currentReelsData[idx]) {
          currentReelsData[idx] = Object.assign({}, currentReelsData[idx], r);
        } else {
          currentReelsData.push(r);
        }
        
        // Update DOM card if it exists
        const card = document.querySelector(`.reel-card[data-index="${idx}"]`);
        if (card) {
          const v = card.querySelector('.reel-video');
          if (v && r.video) v.src = r.video;
          if (v && r.poster) v.poster = r.poster;
          const t = card.querySelector('.reel-title');
          if (t && r.title) t.textContent = r.title;
          const d = card.querySelector('.reel-desc');
          if (d && (r.caption || r.desc)) d.textContent = r.caption || r.desc;
          const p = card.querySelector('.reel-pill');
          if (p && r.tag) p.textContent = '✦ ' + r.tag;
        }
      });
    }

    // Update Gallery
    if (data.gallery && Array.isArray(data.gallery)) {
      const gallerySection = document.getElementById('gallery-section');
      const galleryGrid = document.getElementById('gallery-grid');
      if (galleryGrid) {
        if (data.gallery.length === 0) {
          if (gallerySection) gallerySection.style.display = 'none';
        } else {
          if (gallerySection) gallerySection.style.display = 'block';
          galleryGrid.innerHTML = data.gallery.map(img => `
            <div class="gallery-item" onclick="openLightbox('${img}')">
              <img src="${img}" alt="Ashram Life">
            </div>
          `).join('');
        }
      }
    }
  } catch (error) {
    console.error("Error loading dynamic content:", error);
  }
}

// Lightbox feature
function openLightbox(src) {
  const lightbox = document.createElement('div');
  lightbox.style.position = 'fixed';
  lightbox.style.top = '0';
  lightbox.style.left = '0';
  lightbox.style.width = '100vw';
  lightbox.style.height = '100vh';
  lightbox.style.backgroundColor = 'rgba(0,0,0,0.9)';
  lightbox.style.display = 'flex';
  lightbox.style.alignItems = 'center';
  lightbox.style.justifyContent = 'center';
  lightbox.style.zIndex = '9999';
  lightbox.style.cursor = 'pointer';
  
  const img = document.createElement('img');
  img.src = src;
  img.style.maxWidth = '90%';
  img.style.maxHeight = '90%';
  img.style.objectFit = 'contain';
  img.style.borderRadius = '8px';
  img.style.boxShadow = '0 0 30px rgba(0,0,0,0.5)';
  
  lightbox.appendChild(img);
  lightbox.onclick = () => lightbox.remove();
  document.body.appendChild(lightbox);
}

/* ===== SUBMIT DISCOVERY FORM ===== */
function submitDiscoveryForm(e) {
  e.preventDefault();
  const btn = document.getElementById('dsubmitBtn');
  const msg = document.getElementById('discoveryMsg');
  const form = document.getElementById('discoveryForm');
  
  btn.textContent = 'Sending…';
  btn.disabled = true;
  msg.className = 'discovery-message hidden';
  msg.textContent = '';
  
  const contactVal = document.getElementById('dcontact').value;
  const isEmail = contactVal.includes('@');
  
  const formData = new FormData();
  formData.append('name', document.getElementById('dname').value);
  formData.append('email', isEmail ? contactVal : 'no-email-provided@himyog.com');
  formData.append('phone', isEmail ? '' : contactVal);
  formData.append('program', document.getElementById('dprog').value);
  formData.append('message', 'Inquiry from Hero Banner Discovery Form.\nPreferred Batch: ' + document.getElementById('dbatch').value + '\nContact Info: ' + contactVal);
  
  fetch('contact.php', {
    method: 'POST',
    body: formData
  })
  .then(response => {
    if (response.ok) {
      msg.textContent = '✅ Journey started! We will reach out on WhatsApp/Email within 24 hours.';
      msg.className = 'discovery-message success';
      form.reset();
      updateDiscoveryBatches(); // Reset options
    } else {
      throw new Error('Server response error');
    }
  })
  .catch(err => {
    msg.textContent = '❌ Error submitting. Please try again or WhatsApp us.';
    msg.className = 'discovery-message error';
  })
  .finally(() => {
    btn.textContent = 'Begin Journey →';
    btn.disabled = false;
  });
}

/* ===== QUIZ ===== */
const quizResults = {
  '200h': {
    title: '🎓 The 200-Hour Foundational YTTC is for You',
    desc: 'Launch your teaching career or deepen your personal practice with our Yoga Alliance RYS 200 certification. Includes full lodging, meals, and yogic studies under Yogi Shivam.'
  },
  '300h': {
    title: '🏔️ The 300-Hour Advanced YTTC Awaits You',
    desc: 'Deepen your existing 200-Hour foundation with advanced asana sequencing, Shatkarmas, pranayama, and spiritual philosophy under Yogi Shivam.'
  }
};

function selectQuiz(el, type) {
  document.querySelectorAll('.qcard').forEach(c => c.classList.remove('active'));
  el.classList.add('active');

  const result = document.getElementById('quiz-result');
  const data = quizResults[type];
  result.innerHTML = `<h4>${data.title}</h4><p>${data.desc}</p><a href="#booking" class="btn-primary sm" style="margin-top:1rem;display:inline-block">Apply Now →</a>`;
  result.classList.remove('hidden');
  result.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

/* ===== FAQ ===== */
function toggleFaq(el) {
  const isOpen = el.classList.contains('open');
  document.querySelectorAll('.fitem.open').forEach(item => item.classList.remove('open'));
  if (!isOpen) el.classList.add('open');
}

/* ===== PRICING TABS ===== */
function switchPricing(plan, btn) {
  document.querySelectorAll('.ptab').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  document.getElementById('p200').classList.toggle('hidden', plan !== '200');
  document.getElementById('p300').classList.toggle('hidden', plan !== '300');
}

/* ===== BOOKING FORM ===== */
function submitForm(e) {
  e.preventDefault();
  const btn = document.getElementById('submitBtn');
  const form = document.getElementById('bookingForm');
  
  btn.textContent = 'Sending…';
  btn.disabled = true;

  const formData = new FormData();
  formData.append('name', document.getElementById('fname').value);
  formData.append('email', document.getElementById('femail').value);
  formData.append('phone', document.getElementById('fphone').value);
  formData.append('program', document.getElementById('fprog').value);
  formData.append('message', document.getElementById('fmsg').value);

  fetch('contact.php', {
    method: 'POST',
    body: formData
  })
  .then(response => {
    if (response.ok) {
      btn.textContent = '✅ Enquiry Sent! We\'ll reply within 24 hrs.';
      btn.style.background = 'linear-gradient(135deg,#4caf50,#2e7d32)';
      form.reset();
    } else {
      throw new Error('Network response was not ok.');
    }
  })
  .catch(error => {
    btn.textContent = '❌ Error sending. Please try again.';
    btn.style.background = 'linear-gradient(135deg,#f44336,#d32f2f)';
  })
  .finally(() => {
    setTimeout(() => {
      btn.textContent = 'Send Enquiry & Reserve →';
      btn.style.background = '';
      btn.disabled = false;
    }, 5000);
  });
}



/* ===== MOBILE APP OPTIMIZATIONS ===== */
// Close menu on link click
document.querySelectorAll('.nav-links a').forEach(link => {
  link.addEventListener('click', () => {
    navLinks.classList.remove('open');
    hamburger.classList.remove('active');
  });
});

// Scroll Spy for Bottom Nav
const mbItems = document.querySelectorAll('.mb-item');
const sections = document.querySelectorAll('section');

window.addEventListener('scroll', () => {
  let current = "";
  sections.forEach(section => {
    const sectionTop = section.offsetTop;
    const sectionHeight = section.clientHeight;
    if (pageYOffset >= (sectionTop - 200)) {
      current = section.getAttribute('id');
    }
  });

  mbItems.forEach(item => {
    item.classList.remove('active');
    if (item.getAttribute('href').includes(current)) {
      item.classList.add('active');
    }
  });
});

/* ===== ENQUIRY MODAL POPUP ===== */
function openEnquiryModal(program, room, batch = '') {
  const modal = document.getElementById('enquiryModal');
  const title = document.getElementById('modalTitle');
  const sub = document.getElementById('modalSub');
  const modalProgInput = document.getElementById('modalProg');
  const modalRoomInput = document.getElementById('modalRoom');
  
  if (!modal || !title || !sub) return;
  
  // Set values
  modalProgInput.value = program;
  modalRoomInput.value = room || batch || '';
  
  const displayDetail = [program, room, batch].filter(Boolean).join(' · ');
  sub.textContent = displayDetail;

  // Set WhatsApp dynamic button link
  const modalWaBtn = document.getElementById('modalWaBtn');
  if (modalWaBtn) {
    let msg = `Namaste! I'm interested in enquiring about the ${program}`;
    if (batch) msg += ` for the ${batch} batch`;
    if (room) msg += ` (${room})`;
    msg += ` in Dharamsala.`;
    const waText = encodeURIComponent(msg);
    modalWaBtn.href = `https://wa.me/918894787858?text=${waText}`;
  }
  
  // Show modal and lock body scroll
  modal.classList.remove('hidden');
  document.body.classList.add('no-scroll');
}

function closeEnquiryModal() {
  const modal = document.getElementById('enquiryModal');
  const msg = document.getElementById('modalMsg');
  const form = document.getElementById('modalForm');
  
  if (!modal) return;
  
  modal.classList.add('hidden');
  document.body.classList.remove('no-scroll');
  
  // Reset message and form
  if (msg) {
    msg.className = 'discovery-message hidden';
    msg.textContent = '';
  }
  if (form) {
    form.reset();
  }
}

// Close on outside click
document.addEventListener('DOMContentLoaded', () => {
  const modal = document.getElementById('enquiryModal');
  if (modal) {
    modal.addEventListener('click', (e) => {
      if (e.target === modal) {
        closeEnquiryModal();
      }
    });
  }
});

function submitModalEnquiry(e) {
  e.preventDefault();
  const btn = document.getElementById('modalSubmitBtn');
  const msg = document.getElementById('modalMsg');
  const form = document.getElementById('modalForm');
  
  if (!btn || !msg || !form) return;
  
  btn.textContent = 'Sending…';
  btn.disabled = true;
  msg.className = 'discovery-message hidden';
  msg.textContent = '';
  
  const contactVal = document.getElementById('modalContact').value;
  const nameVal = document.getElementById('modalName').value;
  const progVal = document.getElementById('modalProg').value;
  const roomVal = document.getElementById('modalRoom').value;
  const isEmail = contactVal.includes('@');
  
  const formData = new FormData();
  formData.append('name', nameVal);
  formData.append('email', isEmail ? contactVal : 'no-email-provided@himyog.com');
  formData.append('phone', isEmail ? '' : contactVal);
  formData.append('program', progVal + ' (' + roomVal + ')');
  formData.append('message', 'Enquiry from Pricing Card Modal Popup.\nSelected Program: ' + progVal + '\nSelected Package: ' + roomVal + '\nContact Details: ' + contactVal);
  
  fetch('contact.php', {
    method: 'POST',
    body: formData
  })
  .then(response => {
    if (response.ok) {
      msg.textContent = '✅ Reservation Request Sent! We will contact you on WhatsApp/Email within 24 hours.';
      msg.className = 'discovery-message success';
      form.reset();
      
      // Auto close after 3 seconds
      setTimeout(() => {
        closeEnquiryModal();
      }, 3000);
    } else {
      throw new Error('Server response error');
    }
  })
  .catch(err => {
    msg.textContent = '❌ Error submitting. Please try again or WhatsApp us directly.';
    msg.className = 'discovery-message error';
  })
  .finally(() => {
    btn.textContent = 'Send Reservation Request →';
    btn.disabled = false;
  });
}

// ==================== VIDEO REELS LOGIC ====================
let currentReelsData = [
  {
    video: "uploads/reel1.mp4",
    poster: "practice.png",
    tag: "✦ Daily Sadhana",
    title: "Sunrise Prana Flow",
    desc: "Morning asana & breath alignment in Bhagsu"
  },
  {
    video: "uploads/reel2.mp4",
    poster: "slide2.png",
    tag: "✦ Pranayama Lab",
    title: "Himalayan Breathwork",
    desc: "Awakening vital prana with Yogi Shivam"
  },
  {
    video: "uploads/reel3.mp4",
    poster: "slide3.png",
    tag: "✦ Sacred Sound",
    title: "Evening Satsang & Kirtan",
    desc: "Chanting, singing bowls & deep meditation"
  },
  {
    video: "uploads/reel4.mp4",
    poster: "slide4.png",
    tag: "✦ Ashram Life",
    title: "Mindful Mountain Living",
    desc: "Ayurvedic meals & sacred village walks"
  }
];

let activeModalReelIndex = 0;

function initReels() {
  const cards = document.querySelectorAll('.reel-card');
  if (!cards.length) return;

  cards.forEach((card, index) => {
    const video = card.querySelector('.reel-video');
    const fill = card.querySelector('.reel-progress-fill');
    if (!video) return;

    // Card click toggles play/pause
    card.addEventListener('click', (e) => {
      // Don't toggle if clicking audio button or full screen button
      if (e.target.closest('.reel-audio-btn') || e.target.closest('.reel-view-full')) {
        return;
      }
      toggleReelPlay(card, video);
    });

    // Update progress bar
    video.addEventListener('timeupdate', () => {
      if (video.duration && fill) {
        const pct = (video.currentTime / video.duration) * 100;
        fill.style.width = pct + '%';
      }
    });

    // On ended
    video.addEventListener('ended', () => {
      video.currentTime = 0;
      video.play().catch(() => {});
    });

    // Error handling - gracefully keep card interactive if video file not yet present
    video.addEventListener('error', () => {
      card.classList.remove('is-playing');
    });
  });

  // Autoplay/Pause observer for performance
  if ('IntersectionObserver' in window) {
    const reelObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        const card = entry.target;
        const video = card.querySelector('.reel-video');
        if (!video) return;

        if (entry.isIntersecting) {
          // Attempt muted playback if in view
          video.muted = true;
          const playPromise = video.play();
          if (playPromise !== undefined) {
            playPromise.then(() => {
              card.classList.add('is-playing');
            }).catch(() => {
              card.classList.remove('is-playing');
            });
          }
        } else {
          // Pause when leaving view
          video.pause();
          card.classList.remove('is-playing');
        }
      });
    }, { threshold: 0.45 });

    cards.forEach(card => reelObserver.observe(card));
  }
}

function toggleReelPlay(card, video) {
  if (video.paused) {
    // Pause other inline videos first
    document.querySelectorAll('.reel-card .reel-video').forEach(v => {
      if (v !== video) {
        v.pause();
        const parent = v.closest('.reel-card');
        if (parent) parent.classList.remove('is-playing');
      }
    });

    video.play().then(() => {
      card.classList.add('is-playing');
    }).catch(err => {
      console.log("Reel video play prevented or file not present:", err);
      const isCard0 = card.dataset.index === "0";
      if (isCard0) {
        showReelToast("💡 Drop your video into uploads/reel1.mp4 or upload via Admin!");
      }
    });
  } else {
    video.pause();
    card.classList.remove('is-playing');
  }
}

function toggleReelAudio(btn, e) {
  e.stopPropagation();
  const card = btn.closest('.reel-card');
  if (!card) return;
  const video = card.querySelector('.reel-video');
  if (!video) return;

  const isMuted = video.muted;
  
  if (isMuted) {
    document.querySelectorAll('.reel-card .reel-video').forEach(v => {
      v.muted = true;
      const b = v.closest('.reel-card')?.querySelector('.reel-audio-btn');
      if (b) updateAudioBtnState(b, true);
    });
    video.muted = false;
    updateAudioBtnState(btn, false);
    if (video.paused) {
      video.play().then(() => card.classList.add('is-playing')).catch(() => {});
    }
  } else {
    video.muted = true;
    updateAudioBtnState(btn, true);
  }
}

function updateAudioBtnState(btn, isMuted) {
  const iconMuted = btn.querySelector('.audio-icon-muted');
  const iconUnmuted = btn.querySelector('.audio-icon-unmuted');
  if (iconMuted && iconUnmuted) {
    if (isMuted) {
      iconMuted.style.display = 'block';
      iconUnmuted.style.display = 'none';
      btn.style.background = 'rgba(8, 10, 18, 0.75)';
      btn.style.color = '#FFFFFF';
    } else {
      iconMuted.style.display = 'none';
      iconUnmuted.style.display = 'block';
      btn.style.background = 'var(--gold)';
      btn.style.color = '#080A12';
    }
  }
}

function openReelModal(index, e) {
  if (e) e.stopPropagation();
  activeModalReelIndex = index;
  
  // Pause all inline videos
  document.querySelectorAll('.reel-card .reel-video').forEach(v => {
    v.pause();
    v.closest('.reel-card')?.classList.remove('is-playing');
  });

  const modal = document.getElementById('reelModal');
  const modalVideo = document.getElementById('reelModalVideo');
  const modalTag = document.getElementById('modalReelTag');
  const modalTitle = document.getElementById('modalReelTitle');
  const modalDesc = document.getElementById('modalReelDesc');

  if (!modal || !modalVideo) return;

  const reel = currentReelsData[index] || currentReelsData[0];
  modalVideo.src = reel.video;
  modalVideo.poster = reel.poster || '';
  modalVideo.muted = false; // with sound in modal
  
  if (modalTag) modalTag.textContent = reel.tag;
  if (modalTitle) modalTitle.textContent = reel.title;
  if (modalDesc) modalDesc.textContent = reel.desc || reel.caption || '';

  modal.classList.remove('hidden');
  modal.style.display = 'flex';
  document.body.style.overflow = 'hidden';

  modalVideo.play().catch(err => {
    console.log("Modal video autoplay caught:", err);
  });
}

function closeReelModal(e) {
  if (e && e.target && (e.target.closest('.reel-modal-video-box') || e.target.closest('.reel-modal-details'))) {
    return;
  }
  const modal = document.getElementById('reelModal');
  const modalVideo = document.getElementById('reelModalVideo');
  if (modalVideo) {
    modalVideo.pause();
    modalVideo.currentTime = 0;
  }
  if (modal) {
    modal.classList.add('hidden');
    modal.style.display = 'none';
  }
  document.body.style.overflow = '';
}

function prevReelModal(e) {
  if (e) e.stopPropagation();
  activeModalReelIndex = (activeModalReelIndex - 1 + currentReelsData.length) % currentReelsData.length;
  openReelModal(activeModalReelIndex);
}

function nextReelModal(e) {
  if (e) e.stopPropagation();
  activeModalReelIndex = (activeModalReelIndex + 1) % currentReelsData.length;
  openReelModal(activeModalReelIndex);
}

// Keyboard shortcuts for modal
window.addEventListener('keydown', (e) => {
  const modal = document.getElementById('reelModal');
  if (modal && !modal.classList.contains('hidden') && modal.style.display !== 'none') {
    if (e.key === 'Escape') closeReelModal();
    if (e.key === 'ArrowLeft') prevReelModal();
    if (e.key === 'ArrowRight') nextReelModal();
    if (e.key === ' ') {
      e.preventDefault();
      const mv = document.getElementById('reelModalVideo');
      if (mv) {
        if (mv.paused) mv.play();
        else mv.pause();
      }
    }
  }
});

function showReelToast(text) {
  let toast = document.getElementById('reelToast');
  if (!toast) {
    toast = document.createElement('div');
    toast.id = 'reelToast';
    toast.style.position = 'fixed';
    toast.style.bottom = '90px';
    toast.style.left = '50%';
    toast.style.transform = 'translateX(-50%)';
    toast.style.backgroundColor = 'rgba(15, 23, 42, 0.96)';
    toast.style.color = '#FFD700';
    toast.style.border = '1px solid #D4AF37';
    toast.style.padding = '12px 24px';
    toast.style.borderRadius = '50px';
    toast.style.fontFamily = 'Jost, sans-serif';
    toast.style.fontSize = '0.9rem';
    toast.style.boxShadow = '0 10px 25px rgba(0,0,0,0.5)';
    toast.style.zIndex = '10001';
    toast.style.transition = 'opacity 0.3s ease';
    document.body.appendChild(toast);
  }
  toast.textContent = text;
  toast.style.opacity = '1';
  toast.style.display = 'block';
  setTimeout(() => {
    toast.style.opacity = '0';
    setTimeout(() => { toast.style.display = 'none'; }, 300);
  }, 4500);
}


