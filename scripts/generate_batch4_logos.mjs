import fs from 'fs';
import path from 'path';

const logos = [
  { name: 'unimal.svg', color1: '#15803D', color2: '#F59E0B', accent: '#FFFFFF', text: 'UNIMAL', sub: 'ACEH' },
  { name: 'ubsp.svg', color1: '#059669', color2: '#0284C7', accent: '#FFFFFF', text: 'UBSP', sub: 'MOJOKERTO' },
  { name: 'ubh.svg', color1: '#1E3A8A', color2: '#DC2626', accent: '#FFFFFF', text: 'UBH', sub: 'PADANG' },
  { name: 'uic.svg', color1: '#065F46', color2: '#F59E0B', accent: '#FFFFFF', text: 'UIC', sub: 'JAKARTA' },
  { name: 'ueu.svg', color1: '#EA580C', color2: '#1E3A8A', accent: '#FFFFFF', text: 'ESA UNGGUL', sub: 'JAKARTA' },
  { name: 'utu.svg', color1: '#15803D', color2: '#FBBF24', accent: '#FFFFFF', text: 'UTU', sub: 'MEULABOH' },
  { name: 'ista.svg', color1: '#0284C7', color2: '#F59E0B', accent: '#FFFFFF', text: 'ISTA', sub: 'JAKARTA' },
  { name: 'itats.svg', color1: '#1E40AF', color2: '#F59E0B', accent: '#FFFFFF', text: 'ITATS', sub: 'SURABAYA' },
  { name: 'ujb.svg', color1: '#B91C1C', color2: '#F59E0B', accent: '#FFFFFF', text: 'JANABADRA', sub: 'YOGYAKARTA' },
  { name: 'jayabaya.svg', color1: '#1E3A8A', color2: '#EAB308', accent: '#FFFFFF', text: 'JAYABAYA', sub: 'JAKARTA' },
  { name: 'machung.svg', color1: '#0284C7', color2: '#DC2626', accent: '#FFFFFF', text: 'MA CHUNG', sub: 'MALANG' },
  { name: 'umaha.svg', color1: '#15803D', color2: '#F59E0B', accent: '#FFFFFF', text: 'UMAHA', sub: 'SIDOARJO' },
  { name: 'mahendradatta.svg', color1: '#B91C1C', color2: '#F59E0B', accent: '#FFFFFF', text: 'MAHENDRADATTA', sub: 'BALI' },
  { name: 'moestopo.svg', color1: '#DC2626', color2: '#1E3A8A', accent: '#FFFFFF', text: 'MOESTOPO', sub: 'BERAGAMA' },
  { name: 'uhn.svg', color1: '#1E3A8A', color2: '#F59E0B', accent: '#FFFFFF', text: 'NOMMENSEN', sub: 'MEDAN' },
  { name: 'unpab.svg', color1: '#15803D', color2: '#F59E0B', accent: '#FFFFFF', text: 'UNPAB', sub: 'MEDAN' },
  { name: 'paramadina.svg', color1: '#0F766E', color2: '#D97706', accent: '#FFFFFF', text: 'PARAMADINA', sub: 'JAKARTA' },
  { name: 'pasim.svg', color1: '#1E40AF', color2: '#DC2626', accent: '#FFFFFF', text: 'PASIM', sub: 'BANDUNG' },
  { name: 'uhw-perbanas.svg', color1: '#1E3A8A', color2: '#F59E0B', accent: '#FFFFFF', text: 'UHW PERBANAS', sub: 'SURABAYA' },
  { name: 'perbanas-jkt.svg', color1: '#1E3A8A', color2: '#D97706', accent: '#FFFFFF', text: 'PERBANAS', sub: 'JAKARTA' },
  { name: 'prasetiyamulya.svg', color1: '#0F172A', color2: '#B45309', accent: '#FFFFFF', text: 'PRASETIYA', sub: 'MULYA' },
  { name: 'pnl.svg', color1: '#0284C7', color2: '#F59E0B', accent: '#FFFFFF', text: 'PNL', sub: 'LHOKSEUMAWE' },
  { name: 'pnp.svg', color1: '#1E3A8A', color2: '#F59E0B', accent: '#FFFFFF', text: 'PNP', sub: 'PADANG' },
  { name: 'ulbi.svg', color1: '#EA580C', color2: '#1E3A8A', accent: '#FFFFFF', text: 'ULBI', sub: 'BANDUNG' },
  { name: 'poltesa.svg', color1: '#0284C7', color2: '#10B981', accent: '#FFFFFF', text: 'POLTESA', sub: 'SAMBAS' },
  { name: 'upb-batam.svg', color1: '#1E40AF', color2: '#F59E0B', accent: '#FFFFFF', text: 'UPB', sub: 'BATAM' },
  { name: 'ust-jogja.svg', color1: '#15803D', color2: '#F59E0B', accent: '#FFFFFF', text: 'UST', sub: 'YOGYAKARTA' },
  { name: 'sinus.svg', color1: '#1E3A8A', color2: '#06B6D4', accent: '#FFFFFF', text: 'SINUS', sub: 'SURAKARTA' },
  { name: 'pkn-stan.svg', color1: '#1E3A8A', color2: '#F59E0B', accent: '#FFFFFF', text: 'PKN STAN', sub: 'BANTEN' },
  { name: 'poltekpar-trisakti.svg', color1: '#1E3A8A', color2: '#DC2626', accent: '#FFFFFF', text: 'POLTEKPAR', sub: 'TRISAKTI' },
  { name: 'unklab.svg', color1: '#1E3A8A', color2: '#F59E0B', accent: '#FFFFFF', text: 'UNKLAB', sub: 'MANADO' },
  { name: 'ubhara-jaya.svg', color1: '#B91C1C', color2: '#1E3A8A', accent: '#FFFFFF', text: 'UBHARA JAYA', sub: 'JAKARTA' },
  { name: 'ubm.svg', color1: '#1E3A8A', color2: '#DC2626', accent: '#FFFFFF', text: 'UBM', sub: 'JAKARTA' },
  { name: 'uda.svg', color1: '#15803D', color2: '#F59E0B', accent: '#FFFFFF', text: 'UDA', sub: 'MEDAN' },
  { name: 'uika.svg', color1: '#065F46', color2: '#F59E0B', accent: '#FFFFFF', text: 'UIKA', sub: 'BOGOR' },
  { name: 'uij.svg', color1: '#047857', color2: '#1E3A8A', accent: '#FFFFFF', text: 'UIJ', sub: 'JAKARTA' },
  { name: 'uir.svg', color1: '#059669', color2: '#F59E0B', accent: '#FFFFFF', text: 'UIR', sub: 'PEKANBARU' },
  { name: 'uisu.svg', color1: '#065F46', color2: '#F59E0B', accent: '#FFFFFF', text: 'UISU', sub: 'MEDAN' },
  { name: 'ukdw.svg', color1: '#1E40AF', color2: '#F59E0B', accent: '#FFFFFF', text: 'UKDW', sub: 'YOGYAKARTA' },
  { name: 'uki.svg', color1: '#1E3A8A', color2: '#DC2626', accent: '#FFFFFF', text: 'UKI', sub: 'JAKARTA' },
  { name: 'ukit.svg', color1: '#1D4ED8', color2: '#F59E0B', accent: '#FFFFFF', text: 'UKIT', sub: 'TOMOHON' },
  { name: 'ukrida.svg', color1: '#1E3A8A', color2: '#DC2626', accent: '#FFFFFF', text: 'UKRIDA', sub: 'JAKARTA' },
  { name: 'umi-medan.svg', color1: '#1E3A8A', color2: '#DC2626', accent: '#FFFFFF', text: 'METHODIST', sub: 'MEDAN' },
  { name: 'umj.svg', color1: '#15803D', color2: '#F59E0B', accent: '#FFFFFF', text: 'UMJ', sub: 'JAKARTA' },
  { name: 'umk.svg', color1: '#15803D', color2: '#F59E0B', accent: '#FFFFFF', text: 'UMK', sub: 'KUDUS' },
  { name: 'unimma.svg', color1: '#15803D', color2: '#F59E0B', accent: '#FFFFFF', text: 'UNIMMA', sub: 'MAGELANG' },
  { name: 'umsu.svg', color1: '#15803D', color2: '#F59E0B', accent: '#FFFFFF', text: 'UMSU', sub: 'MEDAN' },
  { name: 'unai.svg', color1: '#1E40AF', color2: '#F59E0B', accent: '#FFFFFF', text: 'UNAI', sub: 'BANDUNG' },
  { name: 'unas.svg', color1: '#1E3A8A', color2: '#F59E0B', accent: '#FFFFFF', text: 'UNAS', sub: 'JAKARTA' },
  { name: 'unham.svg', color1: '#047857', color2: '#F59E0B', accent: '#FFFFFF', text: 'UNHAM', sub: 'MEDAN' },
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
console.log('Batch 4 SVG generation done!');

