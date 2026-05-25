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
  "1st – 24th March 2026",
  "1st – 24th April 2026",
  "1st – 24th May 2026",
  "1st – 24th June 2026",
  "1st – 24th July 2026",
  "1st – 24th September 2026",
  "1st – 24th October 2026"
];

const batches300 = [
  "1st May – 4th June 2026",
  "8th June – 12th July 2026",
  "21st Sept – 25th Oct 2026"
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
});

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
function openEnquiryModal(program, room) {
  const modal = document.getElementById('enquiryModal');
  const title = document.getElementById('modalTitle');
  const sub = document.getElementById('modalSub');
  const modalProgInput = document.getElementById('modalProg');
  const modalRoomInput = document.getElementById('modalRoom');
  
  if (!modal || !title || !sub) return;
  
  // Set values
  modalProgInput.value = program;
  modalRoomInput.value = room;
  sub.textContent = `${program} · ${room}`;
  
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


