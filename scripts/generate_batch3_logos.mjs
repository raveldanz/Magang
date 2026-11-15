import fs from 'fs';
import path from 'path';

const logos = [
  { name: 'polmed.svg', color1: '#1E3A8A', color2: '#F59E0B', accent: '#FFFFFF', text: 'POLMED', sub: 'MEDAN' },
  { name: 'unri.svg', color1: '#15803D', color2: '#FBBF24', accent: '#FFFFFF', text: 'UNRI', sub: 'RIAU' },
  { name: 'unand.svg', color1: '#15803D', color2: '#D97706', accent: '#FFFFFF', text: 'UNAND', sub: 'ANDALAS' },
  { name: 'unp-padang.svg', color1: '#F59E0B', color2: '#1E3A8A', accent: '#FFFFFF', text: 'UNP', sub: 'PADANG' },
  { name: 'unsri.svg', color1: '#F59E0B', color2: '#15803D', accent: '#FFFFFF', text: 'UNSRI', sub: 'SRIWIJAYA' },
  { name: 'polsri.svg', color1: '#1E40AF', color2: '#F59E0B', accent: '#FFFFFF', text: 'POLSRI', sub: 'PALEMBANG' },
  { name: 'unila.svg', color1: '#15803D', color2: '#1E3A8A', accent: '#FFFFFF', text: 'UNILA', sub: 'LAMPUNG' },
  { name: 'itera.svg', color1: '#1E293B', color2: '#D97706', accent: '#FFFFFF', text: 'ITERA', sub: 'SUMATERA' },
  { name: 'unib.svg', color1: '#15803D', color2: '#F59E0B', accent: '#FFFFFF', text: 'UNIB', sub: 'BENGKULU' },
  { name: 'unja.svg', color1: '#EA580C', color2: '#15803D', accent: '#FFFFFF', text: 'UNJA', sub: 'JAMBI' },
  { name: 'umrah.svg', color1: '#0284C7', color2: '#F59E0B', accent: '#FFFFFF', text: 'UMRAH', sub: 'KEPRI' },
  { name: 'polibatam.svg', color1: '#0369A1', color2: '#F97316', accent: '#FFFFFF', text: 'POLIBATAM', sub: 'BATAM' },
  { name: 'unud.svg', color1: '#0F172A', color2: '#DC2626', accent: '#FFFFFF', text: 'UNUD', sub: 'UDAYANA' },
  { name: 'undiksha.svg', color1: '#1E3A8A', color2: '#F59E0B', accent: '#FFFFFF', text: 'UNDIKSHA', sub: 'SINGARAJA' },
  { name: 'pnb.svg', color1: '#1D4ED8', color2: '#10B981', accent: '#FFFFFF', text: 'PNB', sub: 'BALI' },
  { name: 'unram.svg', color1: '#15803D', color2: '#1E3A8A', accent: '#FFFFFF', text: 'UNRAM', sub: 'MATARAM' },
  { name: 'undana.svg', color1: '#B91C1C', color2: '#F59E0B', accent: '#FFFFFF', text: 'UNDANA', sub: 'KUPANG' },
  { name: 'pnk.svg', color1: '#1E40AF', color2: '#F97316', accent: '#FFFFFF', text: 'PNK', sub: 'KUPANG' },
  { name: 'untan.svg', color1: '#15803D', color2: '#EAB308', accent: '#FFFFFF', text: 'UNTAN', sub: 'PONTIANAK' },
  { name: 'polnep.svg', color1: '#1E3A8A', color2: '#059669', accent: '#FFFFFF', text: 'POLNEP', sub: 'PONTIANAK' },
  { name: 'upr.svg', color1: '#15803D', color2: '#DC2626', accent: '#FFFFFF', text: 'UPR', sub: 'PALANGKA RAYA' },
  { name: 'ulm.svg', color1: '#EAB308', color2: '#15803D', accent: '#FFFFFF', text: 'ULM', sub: 'BANJARMASIN' },
  { name: 'poliban.svg', color1: '#1D4ED8', color2: '#F59E0B', accent: '#FFFFFF', text: 'POLIBAN', sub: 'BANJARMASIN' },
  { name: 'unmul.svg', color1: '#15803D', color2: '#F59E0B', accent: '#FFFFFF', text: 'UNMUL', sub: 'SAMARINDA' },
  { name: 'polnes.svg', color1: '#1E3A8A', color2: '#F97316', accent: '#FFFFFF', text: 'POLNES', sub: 'SAMARINDA' },
  { name: 'itk.svg', color1: '#0F172A', color2: '#2563EB', accent: '#FFFFFF', text: 'ITK', sub: 'BALIKPAPAN' },
  { name: 'poltekba.svg', color1: '#1E40AF', color2: '#10B981', accent: '#FFFFFF', text: 'POLTEKBA', sub: 'BALIKPAPAN' },
  { name: 'ubt.svg', color1: '#0369A1', color2: '#F59E0B', accent: '#FFFFFF', text: 'UBT', sub: 'TARAKAN' },
  { name: 'unhas.svg', color1: '#DC2626', color2: '#1E293B', accent: '#FFFFFF', text: 'UNHAS', sub: 'MAKASSAR' },
  { name: 'unm.svg', color1: '#EA580C', color2: '#1E3A8A', accent: '#FFFFFF', text: 'UNM', sub: 'MAKASSAR' },
  { name: 'pnup.svg', color1: '#1E3A8A', color2: '#F59E0B', accent: '#FFFFFF', text: 'PNUP', sub: 'MAKASSAR' },
  { name: 'uin-alauddin.svg', color1: '#065F46', color2: '#10B981', accent: '#FFFFFF', text: 'ALAUDDIN', sub: 'MAKASSAR' },
  { name: 'umi-makassar.svg', color1: '#047857', color2: '#D97706', accent: '#FFFFFF', text: 'UMI', sub: 'MAKASSAR' },
  { name: 'unsrat.svg', color1: '#B91C1C', color2: '#1E3A8A', accent: '#FFFFFF', text: 'UNSRAT', sub: 'MANADO' },
  { name: 'polimdo.svg', color1: '#1E40AF', color2: '#0D9488', accent: '#FFFFFF', text: 'POLIMDO', sub: 'MANADO' },
  { name: 'untad.svg', color1: '#15803D', color2: '#F59E0B', accent: '#FFFFFF', text: 'UNTAD', sub: 'PALU' },
  { name: 'uho.svg', color1: '#047857', color2: '#2563EB', accent: '#FFFFFF', text: 'UHO', sub: 'KENDARI' },
  { name: 'ung.svg', color1: '#991B1B', color2: '#F59E0B', accent: '#FFFFFF', text: 'UNG', sub: 'GORONTALO' },
  { name: 'unsulbar.svg', color1: '#1E3A8A', color2: '#16A34A', accent: '#FFFFFF', text: 'UNSULBAR', sub: 'MAJENE' },
  { name: 'unpatti.svg', color1: '#B91C1C', color2: '#F59E0B', accent: '#FFFFFF', text: 'UNPATTI', sub: 'AMBON' },
  { name: 'unkhair.svg', color1: '#0F172A', color2: '#EA580C', accent: '#FFFFFF', text: 'UNKHAIR', sub: 'TERNATE' },
  { name: 'uncen.svg', color1: '#EAB308', color2: '#15803D', accent: '#FFFFFF', text: 'UNCEN', sub: 'JAYAPURA' },
  { name: 'unipa-papua.svg', color1: '#1E3A8A', color2: '#EAB308', accent: '#FFFFFF', text: 'UNIPA', sub: 'MANOKWARI' },
  { name: 'unmus.svg', color1: '#15803D', color2: '#B91C1C', accent: '#FFFFFF', text: 'UNMUS', sub: 'MERAUKE' },
  { name: 'nusa-putra.svg', color1: '#2563EB', color2: '#F59E0B', accent: '#FFFFFF', text: 'NUSA PUTRA', sub: 'SUKABUMI' },
  { name: 'akprind.svg', color1: '#1E3A8A', color2: '#EA580C', accent: '#FFFFFF', text: 'AKPRIND', sub: 'YOGYAKARTA' },
  { name: 'it-telkom-pwt.svg', color1: '#DC2626', color2: '#1E293B', accent: '#FFFFFF', text: 'IT TELKOM', sub: 'PURWOKERTO' },
  { name: 'undhira.svg', color1: '#0284C7', color2: '#15803D', accent: '#FFFFFF', text: 'UNDHIRA', sub: 'BALI' },
  { name: 'delasalle.svg', color1: '#1E3A8A', color2: '#10B981', accent: '#FFFFFF', text: 'DE LA SALLE', sub: 'MANADO' }
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
console.log('Batch 3 SVG generation done!');

