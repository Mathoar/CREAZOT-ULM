"use client";

import { useMemo, useState } from "react";
import type { PublicReservationPayload } from "./types";

const FR_MONTHS = [
  "janvier", "février", "mars", "avril", "mai", "juin",
  "juillet", "août", "septembre", "octobre", "novembre", "décembre",
];
const FR_DAYS = ["dimanche", "lundi", "mardi", "mercredi", "jeudi", "vendredi", "samedi"];

function formatDateFr(dateStr: string | null): string | null {
  if (!dateStr) return null;
  const d = new Date(`${dateStr}T00:00:00`);
  if (isNaN(d.getTime())) return null;
  return `${FR_DAYS[d.getDay()]} ${d.getDate()} ${FR_MONTHS[d.getMonth()]} ${d.getFullYear()}`;
}

function capitalize(s: string | null): string | null {
  if (!s) return s;
  return s.charAt(0).toUpperCase() + s.slice(1);
}

export function PublicBriefing({ data }: { data: PublicReservationPayload }) {
  const { client, reservation, briefing, circuitBriefing } = data;

  const tabs = useMemo(() => {
    const t: Array<{ key: string; label: string; html: string | null; image: string | null }> = [];
    if (briefing?.html) {
      t.push({ key: "general", label: "Avant le vol", html: briefing.html, image: briefing.headerImage });
    }
    if (circuitBriefing?.html) {
      t.push({
        key: "circuit",
        label: reservation.circuit ? `Le circuit : ${reservation.circuit}` : "Votre circuit",
        html: circuitBriefing.html,
        image: circuitBriefing.image,
      });
    }
    return t;
  }, [briefing, circuitBriefing, reservation.circuit]);

  const [activeKey, setActiveKey] = useState<string>(tabs[0]?.key ?? "general");
  const active = tabs.find((t) => t.key === activeKey) ?? tabs[0];

  const dateFr = formatDateFr(reservation.date);
  const firstName = capitalize(reservation.firstName);

  const heroImage = briefing?.headerImage || null;

  const fullAddress = [
    client.address,
    [client.zipcode, client.city].filter(Boolean).join(" "),
  ]
    .filter(Boolean)
    .join(", ");

  const mapsUrl = briefing?.showMap
    ? fullAddress
      ? `https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(fullAddress)}`
      : client.lat != null && client.lng != null
        ? `https://www.google.com/maps/dir/?api=1&destination=${client.lat},${client.lng}`
        : null
    : null;

  const phoneHref = client.phone ? `tel:${client.phone.replace(/\s+/g, "")}` : null;

  const isHexColor = (c: string | null | undefined): c is string =>
    typeof c === "string" && /^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test(c);
  const brandColor = isHexColor(client.color) ? client.color : "#0284c7";

  return (
    <div className="min-h-screen bg-slate-50 pb-12">
      <div className="relative w-full overflow-hidden" style={{ aspectRatio: "21 / 9", maxHeight: 360, backgroundColor: brandColor }}>
        {heroImage ? (
          <img
            src={heroImage}
            alt={client.name}
            className="w-full h-full object-cover"
            loading="eager"
          />
        ) : (
          <div
            className="w-full h-full"
            style={{
              background: `linear-gradient(135deg, ${brandColor} 0%, ${brandColor} 55%, rgba(0,0,0,0.35) 100%)`,
            }}
          />
        )}
        <div
          className="absolute inset-0"
          style={{
            background: `linear-gradient(to top, ${brandColor}cc 0%, ${brandColor}55 35%, transparent 70%)`,
          }}
        />

        <div className="absolute bottom-0 left-0 right-0 px-4 py-4 sm:px-8 sm:py-6 flex items-center gap-3 sm:gap-4">
          {client.logo && (
            <div className="bg-white rounded-xl p-1.5 shadow-md shrink-0">
              <img src={client.logo} alt={client.name} className="h-10 w-10 sm:h-14 sm:w-14 object-contain" />
            </div>
          )}
          <h1 className="text-white text-lg sm:text-2xl font-semibold drop-shadow-md">
            {client.name}
          </h1>
        </div>
      </div>

      <main className="max-w-3xl mx-auto px-4 -mt-6 relative z-10">
        <section className="bg-white rounded-2xl shadow-lg p-5 sm:p-7 mb-5">
          <p className="text-slate-500 text-sm mb-1">Bonjour {firstName ?? ""},</p>
          <p className="text-slate-800 text-base sm:text-lg leading-relaxed">
            Votre vol est prévu{" "}
            {dateFr && <strong className="text-slate-900">le {dateFr}</strong>}
            {reservation.time && (
              <>
                {" "}à <strong className="text-slate-900">{reservation.time}</strong>
              </>
            )}
            {reservation.circuit && (
              <>
                {" "}— circuit{" "}
                <strong className="text-slate-900">{reservation.circuit}</strong>
              </>
            )}
            .
          </p>
          <p className="text-slate-500 text-sm mt-3">
            Merci de prendre quelques minutes pour lire les informations ci-dessous.
          </p>
        </section>

        {tabs.length > 0 && (
          <section className="bg-white rounded-2xl shadow-lg overflow-hidden mb-5">
            {tabs.length > 1 && (
              <div className="flex border-b border-slate-200 overflow-x-auto">
                {tabs.map((t) => {
                  const isActive = activeKey === t.key;
                  return (
                    <button
                      key={t.key}
                      onClick={() => setActiveKey(t.key)}
                      className={`px-4 py-3 text-sm font-medium whitespace-nowrap transition-colors ${
                        isActive ? "border-b-2" : "text-slate-500 hover:text-slate-700 border-b-2 border-transparent"
                      }`}
                      style={isActive ? { color: brandColor, borderBottomColor: brandColor } : undefined}
                    >
                      {t.label}
                    </button>
                  );
                })}
              </div>
            )}

            <div className="p-5 sm:p-7">
              {active?.image && (
                <img
                  src={active.image}
                  alt={active.label}
                  className="w-full h-auto rounded-xl mb-5"
                  loading="lazy"
                />
              )}
              {active?.html ? (
                <div
                  className="public-briefing-content"
                  dangerouslySetInnerHTML={{ __html: active.html }}
                />
              ) : !active?.image ? (
                <p className="text-slate-500">Aucune information communiquée.</p>
              ) : null}
            </div>
          </section>
        )}

        <section className="bg-white rounded-2xl shadow-lg p-5 sm:p-7">
          <h2 className="text-slate-800 font-semibold mb-4 text-base sm:text-lg">
            Contacts &amp; accès
          </h2>

          <div className="space-y-4">
            {phoneHref && (
              <a
                href={phoneHref}
                className="flex items-center gap-3 -mx-2 px-2 py-2 rounded-lg hover:bg-slate-50 transition-colors group"
              >
                <span
                  className="inline-flex items-center justify-center w-10 h-10 rounded-full text-white shrink-0 text-lg"
                  style={{ backgroundColor: brandColor }}
                >📞</span>
                <div className="flex flex-col flex-1 min-w-0">
                  <span className="text-xs uppercase tracking-wide text-slate-500 font-semibold">Téléphone</span>
                  <span className="font-medium text-slate-800 truncate">{client.phone}</span>
                </div>
                <span className="text-slate-400 text-sm shrink-0 hidden sm:inline">Appeler →</span>
              </a>
            )}

            {(mapsUrl || fullAddress) && (
              <div className="rounded-xl border border-slate-200 overflow-hidden">
                <div className="flex items-start gap-3 p-4 bg-slate-50">
                  <span
                    className="inline-flex items-center justify-center w-10 h-10 rounded-full text-white shrink-0 text-lg"
                    style={{ backgroundColor: brandColor }}
                  >📍</span>
                  <div className="flex flex-col flex-1 min-w-0">
                    <span className="text-xs uppercase tracking-wide text-slate-500 font-semibold">Adresse</span>
                    {client.address && <span className="font-medium text-slate-800">{client.address}</span>}
                    {(client.zipcode || client.city) && (
                      <span className="text-slate-700">
                        {[client.zipcode, client.city].filter(Boolean).join(" ")}
                      </span>
                    )}
                    {!fullAddress && client.lat != null && client.lng != null && (
                      <span className="text-slate-500 text-sm">
                        {client.lat.toFixed(4)}, {client.lng.toFixed(4)}
                      </span>
                    )}
                  </div>
                </div>
                {mapsUrl && (
                  <a
                    href={mapsUrl}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="flex items-center justify-center gap-2 w-full py-3 text-white font-semibold text-sm hover:opacity-90 transition-opacity"
                    style={{ backgroundColor: brandColor }}
                  >
                    <span>🗺️</span>
                    <span>Voir l&apos;itinéraire sur Google Maps</span>
                  </a>
                )}
              </div>
            )}

            {briefing?.extraContacts && (
              <div className="pt-3 border-t border-slate-100 text-slate-600 whitespace-pre-line text-sm leading-relaxed">
                {briefing.extraContacts}
              </div>
            )}

            {!phoneHref && !mapsUrl && !fullAddress && !briefing?.extraContacts && (
              <p className="text-slate-500 text-sm">
                Aucun contact additionnel renseigné.
              </p>
            )}
          </div>
        </section>

        <p className="text-center text-slate-400 text-xs mt-8">
          Page générée par <a href="https://logic-ciel.com" className="hover:text-slate-600 underline">Logic&apos;Ciel</a>
        </p>
      </main>

      <style jsx global>{`
        .public-briefing-content {
          color: #334155;
          line-height: 1.65;
          font-size: 0.95rem;
        }
        .public-briefing-content p { margin: 0 0 0.85rem; }
        .public-briefing-content h1,
        .public-briefing-content h2,
        .public-briefing-content h3 {
          color: #0f172a;
          font-weight: 600;
          margin: 1.4rem 0 0.6rem;
          line-height: 1.3;
        }
        .public-briefing-content h1 { font-size: 1.35rem; }
        .public-briefing-content h2 { font-size: 1.2rem; }
        .public-briefing-content h3 { font-size: 1.05rem; }
        .public-briefing-content ul,
        .public-briefing-content ol {
          margin: 0 0 0.85rem;
          padding-left: 1.4rem;
        }
        .public-briefing-content li { margin-bottom: 0.25rem; }
        .public-briefing-content a {
          color: #0284c7;
          text-decoration: underline;
        }
        .public-briefing-content a:hover { color: #0369a1; }
        .public-briefing-content img {
          max-width: 100%;
          height: auto;
          border-radius: 0.5rem;
          margin: 0.75rem 0;
        }
        .public-briefing-content strong { color: #0f172a; }
        .public-briefing-content blockquote {
          border-left: 3px solid #cbd5e1;
          padding-left: 0.85rem;
          color: #475569;
          font-style: italic;
          margin: 0.85rem 0;
        }
        .public-briefing-content table {
          border-collapse: collapse;
          width: 100%;
          margin: 0.85rem 0;
          font-size: 0.9rem;
        }
        .public-briefing-content th,
        .public-briefing-content td {
          border: 1px solid #e2e8f0;
          padding: 0.5rem 0.75rem;
          text-align: left;
        }
        .public-briefing-content th { background: #f8fafc; font-weight: 600; }
      `}</style>
    </div>
  );
}
