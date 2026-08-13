// =====================================================================
// MCAR - Service Worker
// Giup ung dung cai duoc vao man hinh chinh dien thoai va xu ly
// su kien bam vao thong bao.
// =====================================================================

const PHIEN_BAN = 'mcar-v2';

// Cai dat: khong cache truoc gi ca (du lieu luon phai moi)
self.addEventListener('install', (sk) => {
  self.skipWaiting();
});

// Kich hoat: don cache cu
self.addEventListener('activate', (sk) => {
  sk.waitUntil(
    caches.keys().then((ds) =>
      Promise.all(ds.filter((ten) => ten !== PHIEN_BAN).map((ten) => caches.delete(ten)))
    ).then(() => self.clients.claim())
  );
});

// Bam vao thong bao -> mo tab dang co hoac mo tab moi
self.addEventListener('notificationclick', (sk) => {
  sk.notification.close();
  const duongDan = (sk.notification.data && sk.notification.data.duongDan) || './';

  sk.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then((dsTab) => {
      for (const tab of dsTab) {
        if ('focus' in tab) {
          tab.focus();
          if ('navigate' in tab) tab.navigate(duongDan);
          return;
        }
      }
      if (clients.openWindow) return clients.openWindow(duongDan);
    })
  );
});

// Nhan day tin tu may chu (danh cho ban nang cap sau nay)
self.addEventListener('push', (sk) => {
  let duLieu = { tieuDe: 'MCAR', noiDung: 'Bạn có thông báo mới', duongDan: './' };
  try {
    if (sk.data) duLieu = Object.assign(duLieu, sk.data.json());
  } catch (e) {}

  sk.waitUntil(
    self.registration.showNotification(duLieu.tieuDe, {
      body: duLieu.noiDung,
      icon: 'assets/img/icon-192.png',
      badge: 'assets/img/icon-192.png',
      data: { duongDan: duLieu.duongDan },
      requireInteraction: true,
    })
  );
});
