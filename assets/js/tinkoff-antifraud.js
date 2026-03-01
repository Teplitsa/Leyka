(() => {
  const COOKIE_NAME = 'leyka_tk_af';
  const TTL_DAYS = 7;

  function setCookie(name, value, days) {
    const expires = new Date(Date.now() + days * 864e5).toUTCString();
    document.cookie = `${name}=${encodeURIComponent(value)}; Expires=${expires}; Path=/; SameSite=Lax`;
  }

  function base64urlEncodeUtf8(str) {
    const bytes = new TextEncoder().encode(str);
    let binary = '';
    for (let i = 0; i < bytes.length; i++) {
      binary += String.fromCharCode(bytes[i]);
    }
    const b64 = btoa(binary);
    return b64.replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/g, '');
  }

  function getCookieNames() {
    const raw = document.cookie || '';
    if (!raw.trim()) return '';
    const names = raw
      .split(';')
      .map((p) => p.split('=')[0].trim())
      .filter(Boolean);

    const uniq = Array.from(new Set(names));
    return uniq.join(',').slice(0, 100);
  }

  async function sha256Hex(str) {
    if (window.crypto?.subtle?.digest) {
      const enc = new TextEncoder().encode(str);
      const buf = await crypto.subtle.digest('SHA-256', enc);
      return Array.from(new Uint8Array(buf))
        .map((b) => b.toString(16).padStart(2, '0'))
        .join('');
    }

    let h = 0;
    for (let i = 0; i < str.length; i++) h = (h * 31 + str.charCodeAt(i)) | 0;
    return String(h >>> 0);
  }

  function getOrCreateDeviceId() {
    const key = 'leyka_tk_device_id';
    let id = localStorage.getItem(key);

    if (!id) {
      id = crypto?.randomUUID
        ? crypto.randomUUID()
        : `${Date.now()}-${Math.random().toString(16).slice(2)}`;
      localStorage.setItem(key, id);
    }

    return id.slice(0, 100);
  }

  function detectOS() {
    const uaData = navigator.userAgentData;
    if (uaData && Array.isArray(uaData.brands)) {
      if (typeof uaData.platform === 'string' && uaData.platform) {
        return uaData.platform.slice(0, 100);
      }
    }

    const ua = (navigator.userAgent || '').toLowerCase();

    if (ua.includes('windows')) return 'Windows';
    if (ua.includes('mac os') || ua.includes('macintosh')) return 'macOS';
    if (ua.includes('android')) return 'Android';
    if (ua.includes('iphone') || ua.includes('ipad') || ua.includes('ipod')) return 'iOS';
    if (ua.includes('cros')) return 'Chrome OS';
    if (ua.includes('linux')) return 'Linux';
    return 'Unknown';
  }

  async function main() {
    try {
      const deviceId = getOrCreateDeviceId();
      const os = detectOS();
      const referrer = (document.referrer || '').slice(0, 100);

      const cookieNames = getCookieNames();
      const cookieHash = (await sha256Hex(document.cookie || '')).slice(0, 100);

      const payload = {
        v: 1,
        deviceId,
        os,
        referrer,
        cookieNames,
        cookieHash,
      };

      setCookie(COOKIE_NAME, base64urlEncodeUtf8(JSON.stringify(payload)), TTL_DAYS);
    } catch (_) {
        
    }
  }

  main();
})();