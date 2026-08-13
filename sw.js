// =====================================================================
// MCAR - Service Worker
// Nhan thong bao day tu may chu ngay ca khi da tat ung dung.
//
// May chu chi gui 1 tin hieu rong. Service Worker se goi nguoc ve may chu
// de lay noi dung thong bao roi hien len man hinh dien thoai.
// =====================================================================

const PHIEN_BAN = 'mcar-v3';

self.addEventListener('install', () => {
  self.skipWaiting();
});

self.addEventListener('activate', (sk) => {
  sk.waitUntil(
    caches.keys()
      .then((ds) => Promise.all(ds.filter((t) => t !== PHIEN_BAN).map((t) => caches.delete(t))))
      .then(() => self.clients.claim())
  );
});

// ---------------------------------------------------------------
// Nhan tin hieu day tu may chu
// ---------------------------------------------------------------
self.addEventListener('push', (sk) => {
  sk.waitUntil(xuLyDay());
});

async function xuLyDay() {
  const macDinh = {
    tieuDe: 'MCAR',
    noiDung: 'Bạn có thông báo mới',
    duongDan: new URL('thongbao', self.registration.scope).href,
  };

  try {
    const dangKy = await self.registration.pushManager.getSubscription();
    if (!dangKy) {
      return hienThongBao(macDinh.tieuDe, macDinh.noiDung, macDinh.duongDan, false, 'mcar-chung');
    }

    const traLoi = await fetch(new URL('thongbao/noidungpush', self.registration.scope).href, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ endpoint: dangKy.endpoint }),
      cache: 'no-store',
    });

    const kq = await traLoi.json();

    if (!kq.ok) {
      return hienThongBao(macDinh.tieuDe, macDinh.noiDung, macDinh.duongDan, false, 'mcar-chung');
    }

    // Khong co thong bao moi -> van phai hien 1 cai (trinh duyet bat buoc)
    if (kq.imLang || !kq.danhSach || !kq.danhSach.length) {
      return hienThongBao(kq.tieuDe, kq.noiDung, kq.duongDan, false, 'mcar-tong-hop');
    }

    // Hien tung thong bao
    for (const tb of kq.danhSach) {
      await hienThongBao(tb.tieuDe, tb.noiDung, tb.duongDan, tb.canXuLy, 'mcar-' + tb.id);
    }

    // Cap nhat so tren huy hieu ung dung (neu thiet bi ho tro)
    if (self.navigator && 'setAppBadge' in self.navigator) {
      try {
        if (kq.chuaDoc > 0) await self.navigator.setAppBadge(kq.chuaDoc);
        else await self.navigator.clearAppBadge();
      } catch (e) {}
    }
  } catch (e) {
    return hienThongBao(macDinh.tieuDe, macDinh.noiDung, macDinh.duongDan, false, 'mcar-chung');
  }
}

function hienThongBao(tieuDe, noiDung, duongDan, canXuLy, nhan) {
  return self.registration.showNotification(tieuDe || 'MCAR', {
    body: noiDung || '',
    icon: new URL('assets/img/icon-192.png', self.registration.scope).href,
    badge: new URL('assets/img/icon-192.png', self.registration.scope).href,
    tag: nhan,
    renotify: true,
    requireInteraction: !!canXuLy,   // viec can lam thi giu tren man hinh
    vibrate: canXuLy ? [200, 100, 200] : [100],
    data: { duongDan: duongDan },
  });
}

// ---------------------------------------------------------------
// Bam vao thong bao -> mo ung dung dung trang
// ---------------------------------------------------------------
self.addEventListener('notificationclick', (sk) => {
  sk.notification.close();
  const duongDan = (sk.notification.data && sk.notification.data.duongDan)
    || new URL('thongbao', self.registration.scope).href;

  sk.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then((dsTab) => {
      for (const tab of dsTab) {
        if ('focus' in tab) {
          tab.focus();
          if ('navigate' in tab) return tab.navigate(duongDan);
          return;
        }
      }
      if (clients.openWindow) return clients.openWindow(duongDan);
    })
  );
});

// ---------------------------------------------------------------
// Trinh duyet doi dia chi day tin -> dang ky lai voi may chu
// ---------------------------------------------------------------
self.addEventListener('pushsubscriptionchange', (sk) => {
  sk.waitUntil(
    (async () => {
      try {
        const khoaCu = sk.oldSubscription && sk.oldSubscription.options
          ? sk.oldSubscription.options.applicationServerKey : null;
        if (!khoaCu) return;

        const dangKyMoi = await self.registration.pushManager.subscribe({
          userVisibleOnly: true,
          applicationServerKey: khoaCu,
        });

        const kh = dangKyMoi.toJSON().keys || {};
        await fetch(new URL('thongbao/dangkypush', self.registration.scope).href, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            endpoint: dangKyMoi.endpoint, p256dh: kh.p256dh, auth: kh.auth,
          }),
        });
      } catch (e) {}
    })()
  );
});
