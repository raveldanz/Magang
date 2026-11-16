import fs from 'fs';
import path from 'path';

const logos = [
  { name: 'unigoro.svg', color1: '#EAB308', color2: '#1E3A8A', accent: '#FFFFFF', text: 'UNIGORO', sub: 'BOJONEGORO' },
  { name: 'unika-soegija.svg', color1: '#1E3A8A', color2: '#F59E0B', accent: '#FFFFFF', text: 'SCU', sub: 'SEMARANG' },
  { name: 'unikal.svg', color1: '#15803D', color2: '#F59E0B', accent: '#FFFFFF', text: 'UNIKAL', sub: 'PEKALONGAN' },
  { name: 'unik-kediri.svg', color1: '#1E40AF', color2: '#DC2626', accent: '#FFFFFF', text: 'UNIK', sub: 'KEDIRI' },
  { name: 'unilak.svg', color1: '#EAB308', color2: '#15803D', accent: '#FFFFFF', text: 'UNILAK', sub: 'PEKANBARU' },
  { name: 'unima.svg', color1: '#1E3A8A', color2: '#DC2626', accent: '#FFFFFF', text: 'UNIMA', sub: 'MANADO' },
  { name: 'uninus.svg', color1: '#065F46', color2: '#F59E0B', accent: '#FFFFFF', text: 'UNINUS', sub: 'BANDUNG' },
  { name: 'unipdu.svg', color1: '#15803D', color2: '#1E3A8A', accent: '#FFFFFF', text: 'UNIPDU', sub: 'JOMBANG' },
  { name: 'unira-pamekasan.svg', color1: '#0284C7', color2: '#F59E0B', accent: '#FFFFFF', text: 'UNIRA', sub: 'MADURA' },
  { name: 'unismuh-makassar.svg', color1: '#15803D', color2: '#0284C7', accent: '#FFFFFF', text: 'UNISMUH', sub: 'MAKASSAR' },
  { name: 'unisri.svg', color1: '#1E3A8A', color2: '#DC2626', accent: '#FFFFFF', text: 'UNISRI', sub: 'SURAKARTA' },
  { name: 'unis-tangerang.svg', color1: '#065F46', color2: '#F59E0B', accent: '#FFFFFF', text: 'UNIS', sub: 'TANGERANG' },
  { name: 'unitomo.svg', color1: '#1E40AF', color2: '#F59E0B', accent: '#FFFFFF', text: 'UNITOMO', sub: 'SURABAYA' },
  { name: 'trilogi.svg', color1: '#0F766E', color2: '#D97706', accent: '#FFFFFF', text: 'TRILOGI', sub: 'JAKARTA' },
  { name: 'unjani.svg', color1: '#15803D', color2: '#F59E0B', accent: '#FFFFFF', text: 'UNJANI', sub: 'CIMAHI' },
  { name: 'unkris.svg', color1: '#1E3A8A', color2: '#F59E0B', accent: '#FFFFFF', text: 'UNKRIS', sub: 'BEKASI' },
  { name: 'unmas-bali.svg', color1: '#B91C1C', color2: '#F59E0B', accent: '#FFFFFF', text: 'UNMAS', sub: 'DENPASAR' },
  { name: 'unmer-madiun.svg', color1: '#1E3A8A', color2: '#DC2626', accent: '#FFFFFF', text: 'UNMER', sub: 'MADIUN' },
  { name: 'um-jember.svg', color1: '#15803D', color2: '#F59E0B', accent: '#FFFFFF', text: 'UM JEMBER', sub: 'JEMBER' },
  { name: 'ummat.svg', color1: '#15803D', color2: '#0284C7', accent: '#FFFFFF', text: 'UMMAT', sub: 'MATARAM' },
  { name: 'wicida.svg', color1: '#1E40AF', color2: '#06B6D4', accent: '#FFFFFF', text: 'WICIDA', sub: 'SAMARINDA' },
  { name: 'unpak.svg', color1: '#15803D', color2: '#F59E0B', accent: '#FFFFFF', text: 'UNPAK', sub: 'BOGOR' },
  { name: 'unr-bali.svg', color1: '#B91C1C', color2: '#1E3A8A', accent: '#FFFFFF', text: 'UNR', sub: 'BALI' },
  { name: 'unsada.svg', color1: '#DC2626', color2: '#1E3A8A', accent: '#FFFFFF', text: 'UNSADA', sub: 'JAKARTA' },
  { name: 'ugj-cirebon.svg', color1: '#1E3A8A', color2: '#F59E0B', accent: '#FFFFFF', text: 'UGJ', sub: 'CIREBON' },
  { name: 'untag-bwi.svg', color1: '#DC2626', color2: '#1E293B', accent: '#FFFFFF', text: 'UNTAG', sub: 'BANYUWANGI' },
  { name: 'untag-crb.svg', color1: '#DC2626', color2: '#1E3A8A', accent: '#FFFFFF', text: 'UNTAG', sub: 'CIREBON' },
  { name: 'uta45-jkt.svg', color1: '#DC2626', color2: '#F59E0B', accent: '#FFFFFF', text: 'UTA 45', sub: 'JAKARTA' },
  { name: 'untag-smd.svg', color1: '#DC2626', color2: '#15803D', accent: '#FFFFFF', text: 'UNTAG', sub: 'SAMARINDA' },
  { name: 'untag-smg.svg', color1: '#DC2626', color2: '#F59E0B', accent: '#FFFFFF', text: 'UNTAG', sub: 'SEMARANG' },
  { name: 'unwiku.svg', color1: '#1E3A8A', color2: '#15803D', accent: '#FFFFFF', text: 'UNWIKU', sub: 'PURWOKERTO' },
  { name: 'up45.svg', color1: '#B91C1C', color2: '#F59E0B', accent: '#FFFFFF', text: 'UP45', sub: 'YOGYAKARTA' },
  { name: 'ups-tegal.svg', color1: '#1E3A8A', color2: '#F59E0B', accent: '#FFFFFF', text: 'UPS', sub: 'TEGAL' },
  { name: 'usi-siantar.svg', color1: '#15803D', color2: '#DC2626', accent: '#FFFFFF', text: 'USI', sub: 'SIANTAR' },
  { name: 'usm-semarang.svg', color1: '#1E40AF', color2: '#F59E0B', accent: '#FFFFFF', text: 'USM', sub: 'SEMARANG' },
  { name: 'uti-lampung.svg', color1: '#DC2626', color2: '#F59E0B', accent: '#FFFFFF', text: 'UTI', sub: 'LAMPUNG' },
  { name: 'untidar.svg', color1: '#15803D', color2: '#F59E0B', accent: '#FFFFFF', text: 'UNTIDAR', sub: 'MAGELANG' },
  { name: 'utp-solo.svg', color1: '#1E3A8A', color2: '#F59E0B', accent: '#FFFFFF', text: 'UTP', sub: 'SURAKARTA' },
  { name: 'uwg-malang.svg', color1: '#0284C7', color2: '#15803D', accent: '#FFFFFF', text: 'UWG', sub: 'MALANG' },
  { name: 'ukwk-malang.svg', color1: '#1E3A8A', color2: '#DC2626', accent: '#FFFFFF', text: 'UKWK', sub: 'MALANG' },
  { name: 'unwira-kupang.svg', color1: '#1E3A8A', color2: '#F59E0B', accent: '#FFFFFF', text: 'UNWIRA', sub: 'KUPANG' },
  { name: 'unidha-malang.svg', color1: '#15803D', color2: '#F59E0B', accent: '#FFFFFF', text: 'UNIDHA', sub: 'MALANG' },
  { name: 'upi-yai.svg', color1: '#1E3A8A', color2: '#DC2626', accent: '#FFFFFF', text: 'UPI YAI', sub: 'JAKARTA' },
  { name: 'yarsi.svg', color1: '#065F46', color2: '#F59E0B', accent: '#FFFFFF', text: 'YARSI', sub: 'JAKARTA' },
  { name: 'udb-solo.svg', color1: '#1E40AF', color2: '#F59E0B', accent: '#FFFFFF', text: 'UDB', sub: 'SURAKARTA' },
  { name: 'univrab.svg', color1: '#047857', color2: '#0284C7', accent: '#FFFFFF', text: 'UNIVRAB', sub: 'PEKANBARU' },
  { name: 'stmik-adhiguna.svg', color1: '#1E3A8A', color2: '#06B6D4', accent: '#FFFFFF', text: 'ADHI GUNA', sub: 'PALU' },
  { name: 'uin-datokarama.svg', color1: '#065F46', color2: '#F59E0B', accent: '#FFFFFF', text: 'DATOKARAMA', sub: 'PALU' },
  { name: 'unisa-palu.svg', color1: '#047857', color2: '#F59E0B', accent: '#FFFFFF', text: 'UNISA', sub: 'PALU' },
  { name: 'unismuh-palu.svg', color1: '#15803D', color2: '#F59E0B', accent: '#FFFFFF', text: 'UNISMUH', sub: 'PALU' },
  { name: 'unsimar.svg', color1: '#1E3A8A', color2: '#F59E0B', accent: '#FFFFFF', text: 'UNSIMAR', sub: 'POSO' },
];

const dir = path.join(process.cwd(), 'public', 'images', 'logos');
if (!fs.existsSync(dir)) {
  fs.mkdirSync(dir, { recursive: true });
}

logos.forEach(l => {
  const filePath = path.join(dir, l.name);
  if (fs.existsSync(filePath)) {
    console.log(`Skipping existing: ${l.name}`);
    return;
  }

  const svgContent = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" width="100%" height="100%">
  <defs>
    <linearGradient id="grad-${l.name.replace('.svg', '')}" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="${l.color1}" />
      <stop offset="100%" stop-color="${l.color2}" />
    </linearGradient>
    <filter id="shadow" x="-10%" y="-10%" width="120%" height="120%">
      <feDropShadow dx="0" dy="4" stdDeviation="4" flood-opacity="0.15" />
    </filter>
  </defs>
  <circle cx="100" cy="100" r="92" fill="url(#grad-${l.name.replace('.svg', '')})" filter="url(#shadow)" />
  <circle cx="100" cy="100" r="82" fill="none" stroke="${l.accent}" stroke-width="3" stroke-opacity="0.8" />
  <circle cx="100" cy="100" r="76" fill="${l.color1}" fill-opacity="0.2" />
  <g fill="${l.accent}">
    <path d="M100 45 L115 75 L145 80 L120 102 L128 135 L100 118 L72 135 L80 102 L55 80 L85 75 Z" fill-opacity="0.25" />
    <circle cx="100" cy="90" r="28" fill="${l.accent}" />
    <circle cx="100" cy="90" r="23" fill="${l.color1}" />
    <text x="100" y="96" font-family="Arial, sans-serif" font-weight="900" font-size="18" fill="${l.accent}" text-anchor="middle" letter-spacing="1">${l.text.slice(0, 3)}</text>
    <text x="100" y="142" font-family="Arial, sans-serif" font-weight="bold" font-size="13" fill="${l.accent}" text-anchor="middle" letter-spacing="1.5">${l.text}</text>
    <text x="100" y="158" font-family="Arial, sans-serif" font-weight="600" font-size="9" fill="${l.color2}" text-anchor="middle" letter-spacing="2">${l.sub}</text>
  </g>
</svg>`;

  fs.writeFileSync(filePath, svgContent, 'utf8');
  console.log(`Generated: ${l.name}`);
});
console.log('Batch 5 SVG generation done!');

