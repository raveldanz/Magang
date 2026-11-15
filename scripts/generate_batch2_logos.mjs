import fs from 'fs';
import path from 'path';

const logos = [
  { name: 'uai.svg', color1: '#047857', color2: '#10B981', accent: '#FFFFFF', text: 'UAI', sub: 'AL-AZHAR' },
  { name: 'pancasila.svg', color1: '#1E3A8A', color2: '#DC2626', accent: '#FFFFFF', text: 'UP', sub: 'PANCASILA' },
  { name: 'bakrie.svg', color1: '#0F172A', color2: '#D97706', accent: '#FFFFFF', text: 'BAKRIE', sub: 'UNIVERSITY' },
  { name: 'umn.svg', color1: '#0284C7', color2: '#0D9488', accent: '#FFFFFF', text: 'UMN', sub: 'NUSANTARA' },
  { name: 'sgu.svg', color1: '#DC2626', color2: '#1E293B', accent: '#FFFFFF', text: 'SGU', sub: 'SWISS GERMAN' },
  { name: 'unpam.svg', color1: '#1D4ED8', color2: '#F59E0B', accent: '#FFFFFF', text: 'UNPAM', sub: 'PAMULANG' },
  { name: 'unpad.svg', color1: '#D97706', color2: '#1E3A8A', accent: '#FFFFFF', text: 'UNPAD', sub: 'PADJADJARAN' },
  { name: 'ipb.svg', color1: '#1E3A8A', color2: '#0D9488', accent: '#FFFFFF', text: 'IPB', sub: 'UNIVERSITY' },
  { name: 'upi.svg', color1: '#DC2626', color2: '#1E293B', accent: '#FFFFFF', text: 'UPI', sub: 'BANDUNG' },
  { name: 'uin-bandung.svg', color1: '#065F46', color2: '#D97706', accent: '#FFFFFF', text: 'UIN SGD', sub: 'BANDUNG' },
  { name: 'polban.svg', color1: '#1E40AF', color2: '#F59E0B', accent: '#FFFFFF', text: 'POLBAN', sub: 'BANDUNG' },
  { name: 'polman-bandung.svg', color1: '#0F172A', color2: '#2563EB', accent: '#FFFFFF', text: 'POLMAN', sub: 'BANDUNG' },
  { name: 'unsika.svg', color1: '#15803D', color2: '#FBBF24', accent: '#FFFFFF', text: 'UNSIKA', sub: 'KARAWANG' },
  { name: 'unsil.svg', color1: '#1E3A8A', color2: '#16A34A', accent: '#FFFFFF', text: 'UNSIL', sub: 'SILIWANGI' },
  { name: 'telkom.svg', color1: '#DC2626', color2: '#475569', accent: '#FFFFFF', text: 'TELKOM', sub: 'UNIVERSITY' },
  { name: 'unpar.svg', color1: '#1E3A8A', color2: '#F59E0B', accent: '#FFFFFF', text: 'UNPAR', sub: 'PARAHYANGAN' },
  { name: 'maranatha.svg', color1: '#1E40AF', color2: '#0D9488', accent: '#FFFFFF', text: 'MARANATHA', sub: 'BANDUNG' },
  { name: 'unpas.svg', color1: '#065F46', color2: '#F59E0B', accent: '#FFFFFF', text: 'UNPAS', sub: 'PASUNDAN' },
  { name: 'unisba.svg', color1: '#047857', color2: '#1E3A8A', accent: '#FFFFFF', text: 'UNISBA', sub: 'BANDUNG' },
  { name: 'widyatama.svg', color1: '#991B1B', color2: '#F59E0B', accent: '#FFFFFF', text: 'UTAMA', sub: 'WIDYATAMA' },
  { name: 'unikom.svg', color1: '#1E3A8A', color2: '#0284C7', accent: '#FFFFFF', text: 'UNIKOM', sub: 'BANDUNG' },
  { name: 'president.svg', color1: '#991B1B', color2: '#1E3A8A', accent: '#FFFFFF', text: 'PRESUNIV', sub: 'PRESIDENT' },
  { name: 'uny.svg', color1: '#1E40AF', color2: '#F59E0B', accent: '#FFFFFF', text: 'UNY', sub: 'YOGYAKARTA' },
  { name: 'upn-jogja.svg', color1: '#15803D', color2: '#F59E0B', accent: '#FFFFFF', text: 'UPN JOGJA', sub: 'VETERAN' },
  { name: 'uin-suka.svg', color1: '#047857', color2: '#D97706', accent: '#FFFFFF', text: 'UIN SUKA', sub: 'YOGYAKARTA' },
  { name: 'isi-jogja.svg', color1: '#7C2D12', color2: '#F59E0B', accent: '#FFFFFF', text: 'ISI', sub: 'YOGYAKARTA' },
  { name: 'uii.svg', color1: '#1E3A8A', color2: '#F59E0B', accent: '#FFFFFF', text: 'UII', sub: 'YOGYAKARTA' },
  { name: 'umy.svg', color1: '#047857', color2: '#DC2626', accent: '#FFFFFF', text: 'UMY', sub: 'YOGYAKARTA' },
  { name: 'uajy.svg', color1: '#1E40AF', color2: '#0284C7', accent: '#FFFFFF', text: 'UAJY', sub: 'ATMA JAYA' },
  { name: 'usd.svg', color1: '#991B1B', color2: '#1E3A8A', accent: '#FFFFFF', text: 'USD', sub: 'SANATA DHARMA' },
  { name: 'uad.svg', color1: '#1D4ED8', color2: '#F59E0B', accent: '#FFFFFF', text: 'UAD', sub: 'AHMAD DAHLAN' },
  { name: 'amikom.svg', color1: '#701A75', color2: '#F59E0B', accent: '#FFFFFF', text: 'AMIKOM', sub: 'YOGYAKARTA' },
  { name: 'unnes.svg', color1: '#F59E0B', color2: '#1E3A8A', accent: '#FFFFFF', text: 'UNNES', sub: 'SEMARANG' },
  { name: 'uin-walisongo.svg', color1: '#065F46', color2: '#10B981', accent: '#FFFFFF', text: 'WALISONGO', sub: 'SEMARANG' },
  { name: 'polines.svg', color1: '#1E3A8A', color2: '#0284C7', accent: '#FFFFFF', text: 'POLINES', sub: 'SEMARANG' },
  { name: 'isi-solo.svg', color1: '#78350F', color2: '#F59E0B', accent: '#FFFFFF', text: 'ISI SOLO', sub: 'SURAKARTA' },
  { name: 'unsoed.svg', color1: '#F59E0B', color2: '#15803D', accent: '#FFFFFF', text: 'UNSOED', sub: 'PURWOKERTO' },
  { name: 'untidar.svg', color1: '#F59E0B', color2: '#991B1B', accent: '#FFFFFF', text: 'UNTIDAR', sub: 'MAGELANG' },
  { name: 'udinus.svg', color1: '#1E3A8A', color2: '#0284C7', accent: '#FFFFFF', text: 'UDINUS', sub: 'SEMARANG' },
  { name: 'unissula.svg', color1: '#047857', color2: '#10B981', accent: '#FFFFFF', text: 'UNISSULA', sub: 'SEMARANG' },
  { name: 'ums.svg', color1: '#1E40AF', color2: '#F59E0B', accent: '#FFFFFF', text: 'UMS', sub: 'SURAKARTA' },
  { name: 'uksw.svg', color1: '#1E3A8A', color2: '#15803D', accent: '#FFFFFF', text: 'UKSW', sub: 'SALATIGA' },
  { name: 'usk.svg', color1: '#F59E0B', color2: '#15803D', accent: '#FFFFFF', text: 'USK', sub: 'SYIAH KUALA' },
  { name: 'usu.svg', color1: '#15803D', color2: '#F59E0B', accent: '#FFFFFF', text: 'USU', sub: 'MEDAN' },
  { name: 'unimed.svg', color1: '#15803D', color2: '#1E3A8A', accent: '#FFFFFF', text: 'UNIMED', sub: 'MEDAN' }
];

const dir = path.resolve('public/images/logos');
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
console.log('Batch 2 SVG generation done!');

