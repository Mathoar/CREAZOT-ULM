"use client";

import { useEffect, useState } from "react";

export default function AuthErrorPage() {
  const [isLoop, setIsLoop] = useState(false);

  useEffect(() => {
    const key = "_authErrorTs";
    const now = Date.now();
    const last = Number(sessionStorage.getItem(key) || "0");

    if (now - last < 15_000) {
      sessionStorage.removeItem(key);
      setIsLoop(true);
      return;
    }

    sessionStorage.setItem(key, String(now));
    window.location.replace("/admin");
  }, []);

  const handleRetry = () => {
    sessionStorage.clear();
    window.location.href = `/api/auth/signin?callbackUrl=${encodeURIComponent(window.location.origin + "/admin")}`;
  };

  if (!isLoop) return null;

  return (
    <div style={{
      display: "flex", flexDirection: "column", alignItems: "center",
      justifyContent: "center", height: "100vh", fontFamily: "system-ui, sans-serif",
      padding: "2rem", textAlign: "center", background: "#f5f5f5",
    }}>
      <div style={{
        background: "white", borderRadius: 12, padding: "2.5rem",
        boxShadow: "0 2px 12px rgba(0,0,0,0.08)", maxWidth: 400,
      }}>
        <h1 style={{ fontSize: "1.4rem", marginBottom: "1rem", color: "#333" }}>
          Session expirée
        </h1>
        <p style={{ color: "#666", marginBottom: "1.5rem", lineHeight: 1.5 }}>
          Votre session a expiré. Veuillez vous reconnecter.
        </p>
        <button
          onClick={handleRetry}
          style={{
            background: "#46B6BF", color: "white", border: "none",
            borderRadius: 8, padding: "0.75rem 2rem", fontSize: "1rem",
            cursor: "pointer", fontWeight: 500,
          }}
        >
          Se reconnecter
        </button>
      </div>
    </div>
  );
}
