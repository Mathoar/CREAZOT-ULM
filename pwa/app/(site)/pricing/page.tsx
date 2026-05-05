"use client";

import { useEffect, useState, useMemo } from "react";
import Link from "next/link";
import axios from "axios";

const API = typeof window !== "undefined" ? window.origin : (process.env.NEXT_PUBLIC_ENTRYPOINT || "");
const H = { Accept: "application/ld+json" };

interface Category { id: number; "@id"?: string; name: string; slug: string; discountPercent?: number; maintenanceDiscount?: number; isDefault?: boolean; }
interface Tier { id: number; pricingCategory: string; minAeronefs: number; maxAeronefs: number | null; pricePerAeronef: number; }
interface Pack { id: number; "@id"?: string; name: string; slug: string; description?: string; isDefault?: boolean; tierGroup?: string; tierOrder?: number; featuresList?: string; }
interface PackPrice { id: number; modulePack: string; pricingCategory: string; monthlyPrice: number; }

const iri = (e: { "@id"?: string; id: number }, r: string) => e["@id"] || `/${r}/${e.id}`;

const TIER_META: Record<string, { label: string; tagline: string; color: string; accent: string; icon: string }> = {
  essentiel: {
    label: "Essentiel",
    tagline: "Conformité réglementaire",
    color: "from-blue-600 to-blue-700",
    accent: "blue",
    icon: "🛡️",
  },
  confort: {
    label: "Confort",
    tagline: "Gestion commerciale",
    color: "from-emerald-600 to-emerald-700",
    accent: "emerald",
    icon: "📈",
  },
  premium: {
    label: "Premium",
    tagline: "Intelligence & Communication",
    color: "from-purple-600 to-purple-700",
    accent: "purple",
    icon: "🚀",
  },
  excellence: {
    label: "Excellence",
    tagline: "IA Avancée",
    color: "from-amber-500 to-amber-600",
    accent: "amber",
    icon: "✨",
  },
};

const TIER_ORDER = ["essentiel", "confort", "premium", "excellence"];

export default function PricingPage() {
  const [categories, setCategories] = useState<Category[]>([]);
  const [tiers, setTiers] = useState<Tier[]>([]);
  const [packs, setPacks] = useState<Pack[]>([]);
  const [prices, setPrices] = useState<PackPrice[]>([]);
  const [loading, setLoading] = useState(true);

  // Simulator state
  const [simCategory, setSimCategory] = useState<string>("");
  const [simAeronefs, setSimAeronefs] = useState(3);
  const [simMaintenance, setSimMaintenance] = useState(0);
  const [simTier, setSimTier] = useState("confort");

  useEffect(() => {
    Promise.all([
      axios.get(`${API}/pricing-categories?pagination=false`, { headers: H }),
      axios.get(`${API}/pricing-tiers?pagination=false&order[minAeronefs]=asc`, { headers: H }),
      axios.get(`${API}/module-packs?pagination=false`, { headers: H }),
      axios.get(`${API}/module-pack-prices?pagination=false`, { headers: H }),
    ]).then(([c, t, p, pp]) => {
      const cats = c.data["hydra:member"] || [];
      setCategories(cats);
      setTiers(t.data["hydra:member"] || []);
      setPacks(p.data["hydra:member"] || []);
      setPrices(pp.data["hydra:member"] || []);
      const def = cats.find((x: Category) => x.isDefault) || cats[0];
      if (def) setSimCategory(iri(def, "pricing-categories"));
      setLoading(false);
    }).catch(() => setLoading(false));
  }, []);

  const getTiers = (catIri: string) =>
    tiers.filter((t) => t.pricingCategory === catIri).sort((a, b) => a.minAeronefs - b.minAeronefs);

  const getPrice = (packIri: string, catIri: string) =>
    prices.find((p) => p.modulePack === packIri && p.pricingCategory === catIri);

  const defaultCat = categories.find((c) => c.isDefault) || categories[0];
  const maxTierCount = Math.max(...categories.map((c) => getTiers(iri(c, "pricing-categories")).length), 0);

  const packsByTier = useMemo(() => {
    const grouped: Record<string, Pack[]> = {};
    for (const tier of TIER_ORDER) grouped[tier] = [];
    for (const pack of packs) {
      const tg = pack.tierGroup || "essentiel";
      if (!grouped[tg]) grouped[tg] = [];
      grouped[tg].push(pack);
    }
    for (const tier of TIER_ORDER) {
      grouped[tier].sort((a, b) => (a.tierOrder ?? 0) - (b.tierOrder ?? 0));
    }
    return grouped;
  }, [packs]);

  const tierPrice = (tierKey: string, catIri: string): number => {
    const tierIdx = TIER_ORDER.indexOf(tierKey);
    let total = 0;
    for (let i = 0; i <= tierIdx; i++) {
      const tierPacks = packsByTier[TIER_ORDER[i]] || [];
      for (const pack of tierPacks) {
        const p = getPrice(iri(pack, "module-packs"), catIri);
        total += p?.monthlyPrice ?? 0;
      }
    }
    return total;
  };

  // Simulator computation
  const simResult = useMemo(() => {
    if (!simCategory || !categories.length) return null;
    const tierList = getTiers(simCategory);
    const totalFleet = simAeronefs + simMaintenance;
    const tier = tierList.find((t) => totalFleet >= t.minAeronefs && (t.maxAeronefs === null || totalFleet <= t.maxAeronefs));
    if (!tier) return null;

    const cat = categories.find((c) => iri(c, "pricing-categories") === simCategory);
    const maintenanceDiscount = cat?.maintenanceDiscount || 0;

    const activeAeronefCost = simAeronefs * tier.pricePerAeronef;
    const maintenanceCost = simMaintenance * tier.pricePerAeronef * (1 - maintenanceDiscount / 100);
    const aeronefTotal = activeAeronefCost + maintenanceCost;

    const packsCost = tierPrice(simTier, simCategory);
    const total = aeronefTotal + packsCost;

    return { tier, activeAeronefCost, maintenanceCost, maintenanceDiscount, aeronefTotal, packsCost, total };
  }, [simCategory, simAeronefs, simMaintenance, simTier, categories, tiers, packs, prices]);

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
      <section className="bg-gradient-to-b from-gray-950 to-gray-900 pb-20 pt-24">
        <div className="mx-auto max-w-4xl px-6 text-center">
          <p className="mb-4 text-sm font-medium uppercase tracking-widest text-cyan-500">Tarification</p>
          <h1 className="text-4xl font-bold text-white md:text-5xl lg:text-6xl">
            Choisissez votre formule
          </h1>
          <p className="mx-auto mt-6 max-w-2xl text-lg leading-relaxed text-gray-400">
            4 offres progressives pour accompagner votre activité,
            de la conformité réglementaire à l&apos;intelligence artificielle.
          </p>
        </div>
      </section>

      {/* Tiers cards */}
      <section className="bg-white py-20">
        <div className="mx-auto max-w-7xl px-6">
          <div className="mx-auto max-w-3xl text-center">
            <h2 className="text-3xl font-bold text-gray-900 md:text-4xl">Nos offres</h2>
            <p className="mt-4 text-base text-gray-500">
              Chaque offre inclut les fonctionnalités de l&apos;offre précédente.
              Passez à la vitesse supérieure quand vous le souhaitez.
            </p>
          </div>

          <div className="mt-14 grid gap-6 md:grid-cols-2 lg:grid-cols-4">
            {TIER_ORDER.map((tierKey, idx) => {
              const meta = TIER_META[tierKey];
              const tierPacks = packsByTier[tierKey] || [];
              const catIri = defaultCat ? iri(defaultCat, "pricing-categories") : "";
              const price = catIri ? tierPrice(tierKey, catIri) : 0;
              const isPopular = tierKey === "confort";

              return (
                <div
                  key={tierKey}
                  className={`relative flex flex-col rounded-2xl border-2 p-6 transition-all hover:shadow-lg ${
                    isPopular ? "border-emerald-500 shadow-md" : "border-gray-200"
                  }`}
                >
                  {isPopular && (
                    <span className="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-emerald-600 px-4 py-1 text-xs font-bold uppercase tracking-wide text-white">
                      Populaire
                    </span>
                  )}

                  <div className={`inline-flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br ${meta.color} text-2xl`}>
                    {meta.icon}
                  </div>

                  <h3 className="mt-4 text-xl font-bold text-gray-900">{meta.label}</h3>
                  <p className="mt-1 text-sm text-gray-500">{meta.tagline}</p>

                  <div className="my-5">
                    {tierKey === "excellence" ? (
                      <div>
                        <span className="text-3xl font-bold text-gray-900">+{price - tierPrice("premium", catIri)}</span>
                        <span className="text-sm text-gray-400"> €/mois</span>
                        <p className="text-xs text-gray-400 mt-1">en plus du Premium</p>
                      </div>
                    ) : (
                      <div>
                        <span className="text-3xl font-bold text-gray-900">{price}</span>
                        <span className="text-sm text-gray-400"> €/mois</span>
                        {price === 0 && <p className="text-xs text-cyan-600 font-medium mt-1">Inclus</p>}
                      </div>
                    )}
                  </div>

                  <div className="h-px bg-gray-100 my-4" />

                  <div className="flex-1 space-y-2">
                    {idx > 0 && (
                      <p className="text-xs font-medium text-gray-400 uppercase mb-2">
                        Tout {TIER_META[TIER_ORDER[idx - 1]].label} +
                      </p>
                    )}
                    {tierPacks.map((pack) => (
                      <div key={pack.id}>
                        <p className="text-sm font-semibold text-gray-700">{pack.name}</p>
                        {pack.featuresList && (
                          <ul className="mt-1 space-y-0.5">
                            {pack.featuresList.split(",").map((f, i) => (
                              <li key={i} className="flex items-start gap-1.5 text-xs text-gray-500">
                                <span className="text-green-500 mt-0.5">✓</span>
                                {f.trim()}
                              </li>
                            ))}
                          </ul>
                        )}
                      </div>
                    ))}
                  </div>

                  <Link
                    href="/register"
                    className={`mt-6 block w-full rounded-xl py-3 text-center text-sm font-bold transition ${
                      isPopular
                        ? "bg-emerald-600 text-white hover:bg-emerald-700"
                        : "bg-gray-100 text-gray-700 hover:bg-gray-200"
                    }`}
                  >
                    Essai gratuit 30 jours
                  </Link>
                </div>
              );
            })}
          </div>
        </div>
      </section>

      {/* Prix par aéronef */}
      <section className="bg-gray-50 py-20">
        <div className="mx-auto max-w-6xl px-6">
          <div className="mx-auto max-w-2xl text-center">
            <h2 className="text-3xl font-bold text-gray-900 md:text-4xl">Prix par aéronef</h2>
            <p className="mt-4 text-base text-gray-500">
              Un tarif dégressif selon la taille de votre flotte.
              Plus vous avez d&apos;aéronefs, moins vous payez par appareil.
            </p>
          </div>

          <div className="mt-14 overflow-hidden rounded-xl border border-gray-200 shadow-sm">
            <table className="w-full">
              <thead>
                <tr className="bg-gray-900 text-white">
                  <th className="px-6 py-4 text-left text-sm font-semibold">Nombre d&apos;aéronefs</th>
                  {categories.map((cat) => (
                    <th key={cat.id} className="px-6 py-4 text-center text-sm font-semibold">
                      {cat.name}
                      {cat.discountPercent ? (
                        <span className="ml-2 rounded-full bg-green-500 px-2.5 py-0.5 text-xs font-bold text-white">
                          -{cat.discountPercent}%
                        </span>
                      ) : null}
                    </th>
                  ))}
                </tr>
              </thead>
              <tbody>
                {Array.from({ length: maxTierCount }).map((_, idx) => {
                  const refTier = defaultCat ? getTiers(iri(defaultCat, "pricing-categories"))[idx] : null;
                  return (
                    <tr key={idx} className={idx % 2 === 0 ? "bg-white" : "bg-gray-50"}>
                      <td className="border-t border-gray-100 px-6 py-4 font-medium text-gray-700">
                        {refTier
                          ? refTier.maxAeronefs
                            ? `${refTier.minAeronefs} à ${refTier.maxAeronefs} aéronefs`
                            : `${refTier.minAeronefs}+ aéronefs`
                          : "—"}
                      </td>
                      {categories.map((cat) => {
                        const tier = getTiers(iri(cat, "pricing-categories"))[idx];
                        return (
                          <td key={cat.id} className="border-t border-gray-100 px-6 py-4 text-center">
                            {tier ? (
                              <>
                                <span className="text-2xl font-bold text-gray-900">{tier.pricePerAeronef}</span>
                                <span className="text-sm text-gray-400"> €/mois</span>
                              </>
                            ) : "—"}
                          </td>
                        );
                      })}
                    </tr>
                  );
                })}

                {categories.some((c) => c.maintenanceDiscount) && (
                  <tr className="bg-amber-50">
                    <td className="border-t border-amber-200 px-6 py-3 font-medium text-amber-800">
                      Aéronef en maintenance
                    </td>
                    {categories.map((cat) => (
                      <td key={cat.id} className="border-t border-amber-200 px-6 py-3 text-center">
                        {cat.maintenanceDiscount ? (
                          <span className="rounded-full bg-amber-200 px-3 py-1 text-sm font-semibold text-amber-800">
                            -{cat.maintenanceDiscount}%
                          </span>
                        ) : "—"}
                      </td>
                    ))}
                  </tr>
                )}
              </tbody>
            </table>
          </div>
        </div>
      </section>

      {/* Simulateur interactif */}
      <section className="bg-white py-20">
        <div className="mx-auto max-w-4xl px-6">
          <div className="mx-auto max-w-2xl text-center">
            <h2 className="text-3xl font-bold text-gray-900 md:text-4xl">Simulateur de coût</h2>
            <p className="mt-4 text-base text-gray-500">
              Estimez votre budget mensuel en quelques clics.
            </p>
          </div>

          <div className="mt-14 rounded-2xl border border-gray-200 bg-gray-50 p-8 shadow-sm">
            <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
              {/* Grille tarifaire */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Grille tarifaire</label>
                <select
                  value={simCategory}
                  onChange={(e) => setSimCategory(e.target.value)}
                  className="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm"
                >
                  {categories.map((cat) => (
                    <option key={cat.id} value={iri(cat, "pricing-categories")}>
                      {cat.name}{cat.discountPercent ? ` (-${cat.discountPercent}%)` : ""}
                    </option>
                  ))}
                </select>
              </div>

              {/* Aéronefs actifs */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Aéronefs actifs: <span className="font-bold text-cyan-700">{simAeronefs}</span>
                </label>
                <input
                  type="range"
                  min={1}
                  max={20}
                  value={simAeronefs}
                  onChange={(e) => setSimAeronefs(Number(e.target.value))}
                  className="w-full accent-cyan-600"
                />
              </div>

              {/* En maintenance */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  En maintenance: <span className="font-bold text-amber-700">{simMaintenance}</span>
                </label>
                <input
                  type="range"
                  min={0}
                  max={5}
                  value={simMaintenance}
                  onChange={(e) => setSimMaintenance(Number(e.target.value))}
                  className="w-full accent-amber-500"
                />
              </div>

              {/* Offre */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Offre choisie</label>
                <select
                  value={simTier}
                  onChange={(e) => setSimTier(e.target.value)}
                  className="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm"
                >
                  {TIER_ORDER.map((t) => (
                    <option key={t} value={t}>{TIER_META[t].label} — {TIER_META[t].tagline}</option>
                  ))}
                </select>
              </div>
            </div>

            {/* Résultat */}
            {simResult && (
              <div className="mt-8 space-y-3">
                <div className="flex items-center justify-between rounded-lg bg-white px-4 py-3 border border-gray-100">
                  <span className="text-sm text-gray-700">
                    {simAeronefs} aéronef{simAeronefs > 1 ? "s" : ""} actif{simAeronefs > 1 ? "s" : ""} × {simResult.tier.pricePerAeronef} €
                  </span>
                  <span className="font-semibold text-gray-900">{simResult.activeAeronefCost.toFixed(2)} €</span>
                </div>

                {simMaintenance > 0 && (
                  <div className="flex items-center justify-between rounded-lg bg-amber-50 px-4 py-3 border border-amber-100">
                    <span className="text-sm text-amber-800">
                      {simMaintenance} en maintenance × {simResult.tier.pricePerAeronef} € (-{simResult.maintenanceDiscount}%)
                    </span>
                    <span className="font-semibold text-amber-800">{simResult.maintenanceCost.toFixed(2)} €</span>
                  </div>
                )}

                <div className="flex items-center justify-between rounded-lg bg-white px-4 py-3 border border-gray-100">
                  <span className="text-sm text-gray-700">
                    Offre {TIER_META[simTier].label}
                  </span>
                  {simResult.packsCost === 0 ? (
                    <span className="font-semibold text-cyan-700">Inclus</span>
                  ) : (
                    <span className="font-semibold text-gray-900">{simResult.packsCost.toFixed(2)} €</span>
                  )}
                </div>

                <div className="h-px bg-gray-200" />

                <div className="flex items-center justify-between px-4 py-4 rounded-lg bg-gradient-to-r from-cyan-50 to-blue-50 border border-cyan-100">
                  <span className="text-base font-bold text-gray-900">Total mensuel estimé</span>
                  <span className="text-3xl font-bold text-cyan-700">
                    {simResult.total.toFixed(2).replace(".", ",")} €
                    <span className="text-sm font-normal text-gray-500"> /mois</span>
                  </span>
                </div>

                <div className="rounded-lg bg-cyan-50 px-4 py-3 text-center border border-cyan-100">
                  <p className="text-sm font-medium text-cyan-700">
                    Essai gratuit 30 jours inclus — sans carte bancaire — sans engagement
                  </p>
                </div>
              </div>
            )}
          </div>
        </div>
      </section>

      {/* Lien vers fonctionnalités */}
      <section className="bg-gray-50 py-16">
        <div className="mx-auto max-w-4xl px-6 text-center">
          <h2 className="text-2xl font-bold text-gray-900 md:text-3xl">Besoin de plus de détails ?</h2>
          <p className="mt-4 text-base text-gray-500">
            Découvrez en détail chaque fonctionnalité de Logic&apos;Ciel et comment elles peuvent transformer votre activité.
          </p>
          <Link
            href="/features"
            className="mt-8 inline-block rounded-xl bg-gray-900 px-8 py-4 text-base font-bold text-white transition hover:bg-gray-800"
          >
            Voir toutes les fonctionnalités →
          </Link>
        </div>
      </section>

      {/* CTA */}
      <section className="bg-gradient-to-br from-cyan-700 to-cyan-800 py-24">
        <div className="mx-auto max-w-3xl px-6 text-center">
          <h2 className="text-3xl font-bold text-white md:text-4xl">Prêt à décoller ?</h2>
          <p className="mt-4 text-lg text-cyan-100">
            Essai gratuit 30 jours · Sans engagement · Sans carte bancaire
          </p>
          <div className="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
            <Link
              href="/register"
              className="rounded-xl bg-white px-8 py-4 text-base font-bold text-cyan-700 transition hover:bg-gray-100"
            >
              Démarrer l&apos;essai gratuit
            </Link>
            <Link
              href="/contact"
              className="text-base font-semibold text-white underline underline-offset-4 transition hover:text-cyan-100"
            >
              Nous contacter →
            </Link>
          </div>
        </div>
      </section>
    </div>
  );
}
