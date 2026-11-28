// Menyimpan data konfigurasi katalog game, mata uang, form input akun, dan daftar paket harga.
window.GAMES = {
 mlbb:{name:'Mobile Legends',currency:'Diamond',color:'#F5C800',img:'assets/img/mlbb.jpg',pos:'center',
  fields:[{id:'uid',label:'User ID',ph:'12345678',pattern:'\\d{5,12}'},{id:'zone',label:'Zone ID',ph:'1234',pattern:'\\d{3,6}'}],
  packs:[['86',22000],['172',44000],['257',65000],['344',87000],['514',130000],['706',175000]]},
 valorant:{name:'Valorant',currency:'VP',color:'#FF6B6B',img:'assets/img/valorant.jpg',pos:'center 35%',
  fields:[{id:'riot',label:'Riot ID',ph:'Nama#TAG',pattern:'.{3,16}#.{3,5}'}],
  packs:[['475',49000],['1000',100000],['2050',200000],['3650',350000],['5350',500000]]},
 steam:{name:'Steam Wallet',currency:'Steam Wallet',color:'#4DA3FF',img:'assets/img/steam.jpg',pos:'center',
  fields:[{id:'steam',label:'Steam ID / Email akun',ph:'akun@email.com',pattern:'.{3,60}'}],
  packs:[['Rp 12.000',14000],['Rp 45.000',50000],['Rp 60.000',66000],['Rp 90.000',98000],['Rp 120.000',130000],['Rp 250.000',265000]]},
 roblox:{name:'Roblox',currency:'Robux',color:'#FF4D4D',img:'assets/img/roblox.jpg',pos:'center 45%',
  fields:[{id:'user',label:'Username Roblox',ph:'username',pattern:'[A-Za-z0-9_]{3,20}'}],
  packs:[['80',15000],['400',75000],['800',150000],['1.700',300000],['4.500',750000]]},
 warframe:{name:'Warframe',currency:'Platinum',color:'#B388FF',img:'assets/img/warframe.jpg',pos:'center 4%',
  fields:[{id:'ign',label:'Nama in-game (Tenno)',ph:'NamaTenno',pattern:'[A-Za-z0-9_\\-]{3,24}'}],
  packs:[['75',15000],['170',33000],['370',68000],['1.000',170000],['2.100',340000]]}
};
