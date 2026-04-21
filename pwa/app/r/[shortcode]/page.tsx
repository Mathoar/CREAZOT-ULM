import type { Metadata } from "next";
import { notFound } from "next/navigation";
import { PublicBriefing } from "./PublicBriefing";
import type { PublicReservationPayload } from "./types";

export const dynamic = "force-dynamic";

const API_URL =
  typeof window !== "undefined"
    ? window.origin
    : process.env.NEXT_PUBLIC_ENTRYPOINT || "https://logic-ciel.com";

export const metadata: Metadata = {
  title: "Votre vol — informations passager",
  robots: { index: false, follow: false },
};

async function fetchReservation(
  shortcode: string,
): Promise<{ status: number; data: PublicReservationPayload | { error: string } | null }> {
  try {
    const res = await fetch(`${API_URL}/r/${shortcode}`, {
      cache: "no-store",
      headers: { Accept: "application/json" },
    });
    const json = await res.json().catch(() => null);
    return { status: res.status, data: json };
  } catch {
    return { status: 500, data: null };
  }
}

export default async function Page({
  params,
}: {
  params: Promise<{ shortcode: string }>;
}) {
  const { shortcode } = await params;

  if (!/^[a-zA-Z0-9]{10}$/.test(shortcode)) {
    notFound();
  }

  const { status, data } = await fetchReservation(shortcode);

  if (status === 200 && data && !("error" in data)) {
    return <PublicBriefing data={data as PublicReservationPayload} />;
  }

  const errorCode =
    data && typeof data === "object" && "error" in data
      ? (data as { error: string }).error
      : status === 429
        ? "too_many_requests"
        : "not_found";

  return <ErrorScreen code={errorCode} />;
}

function ErrorScreen({ code }: { code: string }) {
  const messages: Record<string, { title: string; body: string }> = {
    not_found: {
      title: "Lien introuvable",
      body:
        "Ce lien n'est plus actif ou n'a jamais existé. Si vous avez un vol prévu, merci de contacter directement votre prestataire.",
    },
    expired: {
      title: "Lien expiré",
      body:
        "Cette réservation date de plus de 7 jours, le lien n'est plus accessible. Bons souvenirs de vol !",
    },
    cancelled: {
      title: "Réservation annulée",
      body:
        "Cette réservation a été annulée. En cas de doute, merci de contacter votre prestataire.",
    },
    too_many_requests: {
      title: "Trop de requêtes",
      body: "Patientez quelques secondes puis rafraîchissez la page.",
    },
  };

  const m = messages[code] ?? messages.not_found;

  return (
    <div className="min-h-screen flex items-center justify-center bg-slate-50 px-4 py-12">
      <div className="max-w-md w-full bg-white rounded-2xl shadow-lg p-8 text-center">
        <div className="text-5xl mb-4">✈️</div>
        <h1 className="text-2xl font-semibold text-slate-800 mb-3">{m.title}</h1>
        <p className="text-slate-600 leading-relaxed">{m.body}</p>
      </div>
    </div>
  );
}
