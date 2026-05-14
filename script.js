/* ===== THEME TOGGLE ===== */
const html = document.documentElement;
const themeToggle = document.getElementById('themeToggle');

// Load saved theme or default to dark
const savedTheme = localStorage.getItem('theme') || 'dark';
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

/* ===== QUIZ ===== */
const quizResults = {
  online: {
    title: '✨ Online Classes Are Your Starting Point',
    desc: 'Begin your yoga journey from wherever you are. Build a solid foundation with live-streamed and recorded sessions with Yogi Shivam.'
  },
  hybrid: {
    title: '📅 The Hybrid Program Suits You Perfectly',
    desc: 'Flexible online learning combined with weekend immersions — the ideal path for busy professionals ready to deepen their practice.'
  },
  '7day': {
    title: '🏔️ The 7-Day Lifestyle Retreat Awaits You',
    desc: 'A short but deeply transformative week in the sacred Himalayas. Reset, heal, and reconnect with your inner self.'
  },
  yttc: {
    title: '🎓 You Are Ready for the 200-Hour YTTC',
    desc: 'Launch your yoga teaching career with a globally recognized Yoga Alliance certification. This is where your real journey begins.'
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

/* ===== HERO SLIDER LOGIC ===== */
const heroSlides = document.querySelectorAll('.hero-slide');
const sliderDots = document.querySelectorAll('.slider-dot');
let currentSlide = 0;
let sliderInterval;

function showSlide(index) {
  heroSlides.forEach(s => s.classList.remove('active'));
  sliderDots.forEach(d => d.classList.remove('active'));
  
  heroSlides[index].classList.add('active');
  sliderDots[index].classList.add('active');
  
  // Pause all videos, play current
  document.querySelectorAll('.hero-video').forEach((v, i) => {
    if(i === index) v.play();
    else v.pause();
  });
  
  currentSlide = index;
}

function nextSlide() {
  let next = (currentSlide + 1) % heroSlides.length;
  showSlide(next);
}

function startSlider() {
  stopSlider();
  sliderInterval = setInterval(nextSlide, 8000); // 8 seconds per slide
}

function stopSlider() {
  if(sliderInterval) clearInterval(sliderInterval);
}

sliderDots.forEach(dot => {
  dot.addEventListener('click', () => {
    const index = parseInt(dot.getAttribute('data-index'));
    showSlide(index);
    startSlider(); // Reset timer on manual click
  });
});

// Initialize
if(heroSlides.length > 0) {
  startSlider();
}

