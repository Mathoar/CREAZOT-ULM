"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import axios from "axios";

const API = typeof window !== "undefined" ? window.origin : (process.env.NEXT_PUBLIC_ENTRYPOINT || "");
const H = { Accept: "application/ld+json" };

interface Pack { id: number; name: string; slug: string; description?: string; tierGroup?: string; tierOrder?: number; featuresList?: string; }

const TIER_META: Record<string, { label: string; tagline: string; description: string; gradient: string; icon: string }> = {
  essentiel: {
    label: "Essentiel",
    tagline: "Conformité réglementaire",
    description: "Le socle indispensable pour être conforme à la réglementation ULM 2025 et opérationnel au quotidien. Gestion de flotte, vols, carnets, NOTAM et MANEX.",
    gradient: "from-blue-500 to-blue-600",
    icon: "🛡️",
  },
  confort: {
    label: "Confort",
    tagline: "Gestion commerciale",
    description: "Optimisez votre activité commerciale : réservations en ligne, bons cadeaux, gestion financière, suivi passagers et tracking GPS.",
    gradient: "from-emerald-500 to-emerald-600",
    icon: "📈",
  },
  premium: {
    label: "Premium",
    tagline: "Intelligence & Communication",
    description: "Passez à la vitesse supérieure avec les notifications SMS, l'intelligence artificielle, les caméras terrain et le module formation.",
    gradient: "from-purple-500 to-purple-600",
    icon: "🚀",
  },
  excellence: {
    label: "Excellence",
    tagline: "IA Avancée",
    description: "L'automatisation complète de la relation client grâce aux assistants IA : réservation par email et assistant vocal disponibles 24/7.",
    gradient: "from-amber-500 to-amber-600",
    icon: "✨",
  },
};

const TIER_ORDER = ["essentiel", "confort", "premium", "excellence"];

const DETAILED_FEATURES: Record<string, { title: string; description: string; highlights: string[] }[]> = {
  essentiel: [
    {
      title: "Dashboard opérationnel",
      description: "Vue d'ensemble de votre activité en temps réel : météo, vols du jour, disponibilité des machines.",
      highlights: ["Météo METAR/TAF en direct", "Vols planifiés du jour", "État de la flotte"],
    },
    {
      title: "Gestion de flotte",
      description: "Suivi complet de vos aéronefs : heures de vol, maintenance, documents réglementaires.",
      highlights: ["Carnet d'entretien numérique", "Alertes maintenance", "Documents à jour"],
    },
    {
      title: "Carnets de vol",
      description: "Carnet de vol numérique pour chaque pilote, conforme à la réglementation 2025.",
      highlights: ["Suivi heures individuel", "Export PDF", "Historique complet"],
    },
    {
      title: "Gestion des atterrissages",
      description: "Suivi des mouvements terrain, compatible avec les exigences des gestionnaires d'aérodrome.",
      highlights: ["Enregistrement automatique", "Statistiques trafic", "Export données"],
    },
    {
      title: "NOTAM & Sécurité",
      description: "Consultation des NOTAM en vigueur pour vos terrains, avec analyse de pertinence.",
      highlights: ["Mise à jour automatique", "Filtrage par zone", "Alertes critiques"],
    },
    {
      title: "Manuel d'Exploitation (MANEX)",
      description: "Génération et maintenance du MANEX réglementaire, avec sections personnalisables et versioning.",
      highlights: ["Génération automatique", "Sections éditables", "Export PDF", "Historique versions"],
    },
  ],
  confort: [
    {
      title: "Réservations en ligne",
      description: "Système complet de réservation avec options tarifaires, calendrier interactif et confirmations automatiques.",
      highlights: ["Options photos/vidéo/patrouille", "Calendrier drag & drop", "Confirmation email automatique"],
    },
    {
      title: "Bons cadeaux & Boutique",
      description: "Vendez des bons cadeaux en ligne, intégrez votre boutique Wix et gérez vos partenaires revendeurs.",
      highlights: ["Bons cadeaux personnalisés", "Intégration Wix", "Commissions partenaires"],
    },
    {
      title: "Gestion passagers",
      description: "Inscription passagers conforme RGPD avec fiche de poids, contact d'urgence et suivi des origines.",
      highlights: ["Formulaire RGPD", "Tracking origine", "Statistiques clientèle"],
    },
    {
      title: "Suivi financier",
      description: "Tableau de bord financier complet : encaissements, impayés, dépenses carburant et maintenance.",
      highlights: ["Suivi encaissements", "Alertes impayés", "Ventilation dépenses"],
    },
    {
      title: "Tracking GPS",
      description: "Position en temps réel de vos aéronefs via balise Microtrak, avec historique des trajectoires.",
      highlights: ["Position temps réel", "Historique trajectoires", "Alertes géofencing"],
    },
  ],
  premium: [
    {
      title: "Notifications SMS",
      description: "Envoi groupé de SMS aux passagers (rappels J-1, confirmations, briefings) avec suivi de livraison.",
      highlights: ["Envoi groupé automatisé", "Suivi delivery Twilio", "Templates personnalisables", "Planification J-1"],
    },
    {
      title: "Briefing IA (KIMI)",
      description: "Intelligence artificielle pour l'analyse météo, l'interprétation NOTAM et le calcul du Score OPS.",
      highlights: ["Briefing météo intelligent", "Score OPS automatique", "Interprétation NOTAM"],
    },
    {
      title: "Caméras terrain",
      description: "Intégration de vos caméras terrain pour vérification visuelle des conditions météo en temps réel.",
      highlights: ["Vue directe terrain", "Historique images", "Multi-caméras"],
    },
    {
      title: "Formation pilote",
      description: "Module complet de formation : leçons, programmes, suivi de progression et validation instructeur.",
      highlights: ["Leçons personnalisées", "Programmes structurés", "Grille de progression", "Validation instructeur"],
    },
  ],
  excellence: [
    {
      title: "Assistant email IA",
      description: "L'IA gère vos emails de réservation de manière autonome : compréhension, vérification disponibilité, confirmation.",
      highlights: ["Lecture emails automatique", "Vérification disponibilité", "Réponse intelligente", "Création de réservation"],
    },
    {
      title: "Assistant vocal Vapi",
      description: "Répondeur intelligent disponible 24/7, capable de renseigner les appelants et prendre des réservations par téléphone.",
      highlights: ["Disponible 24/7", "Compréhension naturelle", "Prise de réservation", "Transfert vers pilote si besoin"],
    },
  ],
};

export default function FeaturesPage() {
  const [packs, setPacks] = useState<Pack[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    axios.get(`${API}/module-packs?pagination=false`, { headers: H })
      .then((res) => {
        setPacks(res.data["hydra:member"] || []);
        setLoading(false);
      })
      .catch(() => setLoading(false));
  }, []);

  if (loading) {
    return (
      <div className="flex items-center justify-center py-32">
        <div className="h-10 w-10 animate-spin rounded-full border-4 border-cyan-600 border-t-transparent" />
      </div>
    );
  }

  return (
    <div className="font-sans">
      {/* Hero */}
      <section className="bg-gradient-to-b from-gray-950 to-gray-900 pb-16 pt-24">
        <div className="mx-auto max-w-4xl px-6 text-center">
          <p className="mb-4 text-sm font-medium uppercase tracking-widest text-cyan-500">Fonctionnalités</p>
          <h1 className="text-4xl font-bold text-white md:text-5xl">
            Tout ce dont vous avez besoin pour voler sereinement
          </h1>
          <p className="mx-auto mt-6 max-w-2xl text-lg leading-relaxed text-gray-400">
            Logic&apos;Ciel accompagne les structures ULM de la conformité réglementaire
            jusqu&apos;à l&apos;automatisation intelligente de votre activité.
          </p>
        </div>
      </section>

      {/* Tiers sections */}
      {TIER_ORDER.map((tierKey) => {
        const meta = TIER_META[tierKey];
        const features = DETAILED_FEATURES[tierKey] || [];

        return (
          <section key={tierKey} className="py-20 even:bg-gray-50">
            <div className="mx-auto max-w-6xl px-6">
              {/* Tier header */}
              <div className="flex items-center gap-4 mb-12">
                <div className={`inline-flex h-14 w-14 items-center justify-center rounded-xl bg-gradient-to-br ${meta.gradient} text-3xl shadow-lg`}>
                  {meta.icon}
                </div>
                <div>
                  <h2 className="text-2xl font-bold text-gray-900 md:text-3xl">
                    {meta.label}
                    <span className="ml-3 text-base font-normal text-gray-400">— {meta.tagline}</span>
                  </h2>
                  <p className="mt-1 text-sm text-gray-500 max-w-2xl">{meta.description}</p>
                </div>
              </div>

              {/* Feature cards */}
              <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                {features.map((feature, idx) => (
                  <div key={idx} className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm hover:shadow-md transition-shadow">
                    <h3 className="text-lg font-bold text-gray-900">{feature.title}</h3>
                    <p className="mt-2 text-sm text-gray-500 leading-relaxed">{feature.description}</p>
                    <ul className="mt-4 space-y-1.5">
                      {feature.highlights.map((h, i) => (
                        <li key={i} className="flex items-center gap-2 text-sm text-gray-700">
                          <span className="flex h-5 w-5 items-center justify-center rounded-full bg-green-100 text-green-600 text-xs">✓</span>
                          {h}
                        </li>
                      ))}
                    </ul>
                  </div>
                ))}
              </div>
            </div>
          </section>
        );
      })}

      {/* Réglementation */}
      <section className="bg-blue-50 py-16">
        <div className="mx-auto max-w-4xl px-6">
          <div className="rounded-2xl border border-blue-200 bg-white p-8 shadow-sm">
            <div className="flex items-start gap-4">
              <span className="text-3xl">⚖️</span>
              <div>
                <h3 className="text-xl font-bold text-gray-900">Réglementation ULM - Février 2025</h3>
                <p className="mt-2 text-sm text-gray-600 leading-relaxed">
                  Les nouvelles exigences réglementaires imposent aux structures ULM de maintenir un suivi rigoureux des heures de vol,
                  des qualifications pilotes, de la maintenance et de la documentation opérationnelle (MANEX).
                  L&apos;offre <strong>Essentiel</strong> de Logic&apos;Ciel couvre l&apos;ensemble de ces obligations
                  pour vous permettre d&apos;être en conformité dès le premier jour.
                </p>
                <Link href="/pricing" className="mt-4 inline-block text-sm font-semibold text-blue-600 hover:text-blue-700">
                  Voir les tarifs →
                </Link>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* CTA */}
      <section className="bg-gradient-to-br from-cyan-700 to-cyan-800 py-20">
        <div className="mx-auto max-w-3xl px-6 text-center">
          <h2 className="text-3xl font-bold text-white md:text-4xl">Prêt à simplifier votre gestion ?</h2>
          <p className="mt-4 text-lg text-cyan-100">
            30 jours d&apos;essai gratuit · Toutes les fonctionnalités · Sans engagement
          </p>
          <div className="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
            <Link
              href="/register"
              className="rounded-xl bg-white px-8 py-4 text-base font-bold text-cyan-700 transition hover:bg-gray-100"
            >
              Démarrer l&apos;essai gratuit
            </Link>
            <Link
              href="/pricing"
              className="text-base font-semibold text-white underline underline-offset-4 transition hover:text-cyan-100"
            >
              Voir les tarifs →
            </Link>
          </div>
        </div>
      </section>
    </div>
  );
}
