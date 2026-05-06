"use client";

import { useEffect, useState, useMemo } from "react";
import Link from "next/link";
import axios from "axios";

const API = typeof window !== "undefined" ? window.origin : (process.env.NEXT_PUBLIC_ENTRYPOINT || "");
const H = { Accept: "application/ld+json" };

interface Category { id: number; "@id"?: string; name: string; slug: string; discountPercent?: number; maintenanceDiscount?: number; isDefault?: boolean; }
interface PricingTier { id: number; pricingCategory: string; minAeronefs: number; maxAeronefs: number | null; pricePerAeronef: number; tierGroup?: string; }
interface Pack { id: number; "@id"?: string; name: string; slug: string; description?: string; isDefault?: boolean; tierGroup?: string; tierOrder?: number; featuresList?: string; addonFrom?: string; isAddon?: boolean; }
interface PackPrice { id: number; modulePack: string; pricingCategory: string; monthlyPrice: number; }

const iri = (e: { "@id"?: string; id: number }, r: string) => e["@id"] || `/${r}/${e.id}`;

const TIER_META: Record<string, { label: string; tagline: string; color: string; border: string; bg: string; icon: string }> = {
  essentiel: {
    label: "Essentiel",
    tagline: "Conformité réglementaire & opérationnel",
    color: "text-blue-700",
    border: "border-blue-300",
    bg: "bg-blue-50",
    icon: "🛡️",
  },
  premium: {
    label: "Premium",
    tagline: "Gestion commerciale complète",
    color: "text-purple-700",
    border: "border-purple-300",
    bg: "bg-purple-50",
    icon: "🚀",
  },
};

const TIER_ORDER = ["essentiel", "premium"];

export default function PricingPage() {
  const [categories, setCategories] = useState<Category[]>([]);
  const [pricingTiers, setPricingTiers] = useState<PricingTier[]>([]);
  const [packs, setPacks] = useState<Pack[]>([]);
  const [prices, setPrices] = useState<PackPrice[]>([]);
  const [loading, setLoading] = useState(true);

  const [simCategory, setSimCategory] = useState<string>("");
  const [simAeronefs, setSimAeronefs] = useState(3);
  const [simMaintenance, setSimMaintenance] = useState(0);
  const [simTier, setSimTier] = useState("essentiel");
  const [simAddons, setSimAddons] = useState<string[]>([]);

  useEffect(() => {
    Promise.all([
      axios.get(`${API}/pricing-categories?pagination=false`, { headers: H }),
      axios.get(`${API}/pricing-tiers?pagination=false&order[minAeronefs]=asc`, { headers: H }),
      axios.get(`${API}/module-packs?pagination=false`, { headers: H }),
      axios.get(`${API}/module-pack-prices?pagination=false`, { headers: H }),
    ]).then(([c, t, p, pp]) => {
      const cats = c.data["hydra:member"] || [];
      setCategories(cats);
      setPricingTiers((t.data["hydra:member"] || []).filter((x: PricingTier) => x.tierGroup && !x.tierGroup.startsWith("legacy")));
      setPacks((p.data["hydra:member"] || []).filter((x: Pack) => x.tierGroup !== "hidden"));
      setPrices(pp.data["hydra:member"] || []);
      const def = cats.find((x: Category) => x.isDefault) || cats[0];
      if (def) setSimCategory(iri(def, "pricing-categories"));
      setLoading(false);
    }).catch(() => setLoading(false));
  }, []);

  const defaultCat = categories.find((c) => c.isDefault) || categories[0];

  const getTierPricing = (tierGroup: string, catIri: string) =>
    pricingTiers
      .filter((t) => t.tierGroup === tierGroup && t.pricingCategory === catIri)
      .sort((a, b) => a.minAeronefs - b.minAeronefs);

  const getAeronefPrice = (tierGroup: string, catIri: string, fleetSize: number) => {
    const tiers = getTierPricing(tierGroup, catIri);
    return tiers.find((t) => fleetSize >= t.minAeronefs && (t.maxAeronefs === null || fleetSize <= t.maxAeronefs));
  };

  const packsByTier = useMemo(() => {
    const grouped: Record<string, Pack[]> = {};
    for (const tier of TIER_ORDER) grouped[tier] = [];
    for (const pack of packs) {
      if (pack.addonFrom) continue;
      const tg = pack.tierGroup || "essentiel";
      if (!grouped[tg]) grouped[tg] = [];
      grouped[tg].push(pack);
    }
    for (const tier of TIER_ORDER) {
      grouped[tier]?.sort((a, b) => (a.tierOrder ?? 0) - (b.tierOrder ?? 0));
    }
    return grouped;
  }, [packs]);

  const addonPacks = useMemo(() => packs.filter((p) => !!p.addonFrom), [packs]);

  const getAddonPrice = (packIri: string, catIri: string) =>
    prices.find((p) => p.modulePack === packIri && p.pricingCategory === catIri);

  const simResult = useMemo(() => {
    if (!simCategory || !categories.length) return null;
    const totalFleet = simAeronefs + simMaintenance;
    const tier = getAeronefPrice(simTier, simCategory, totalFleet);
    if (!tier) return null;

    const cat = categories.find((c) => iri(c, "pricing-categories") === simCategory);
    const maintenanceDiscount = cat?.maintenanceDiscount || 0;

    const activeAeronefCost = simAeronefs * tier.pricePerAeronef;
    const maintenanceCost = simMaintenance * tier.pricePerAeronef * (1 - maintenanceDiscount / 100);
    const aeronefTotal = activeAeronefCost + maintenanceCost;

    let addonsTotal = 0;
    const addonDetails: { name: string; price: number }[] = [];
    for (const slug of simAddons) {
      const pack = packs.find((p) => p.slug === slug);
      if (!pack) continue;
      const pp = getAddonPrice(iri(pack, "module-packs"), simCategory);
      const price = pp?.monthlyPrice ?? 0;
      addonsTotal += price;
      addonDetails.push({ name: pack.name, price });
    }

    const total = aeronefTotal + addonsTotal;
    return { tier, activeAeronefCost, maintenanceCost, maintenanceDiscount, aeronefTotal, addonsTotal, addonDetails, total };
  }, [simCategory, simAeronefs, simMaintenance, simTier, simAddons, categories, pricingTiers, packs, prices]);

  const toggleAddon = (slug: string) => {
    setSimAddons((prev) => prev.includes(slug) ? prev.filter((s) => s !== slug) : [...prev, slug]);
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center py-32">
        <div className="h-10 w-10 animate-spin rounded-full border-4 border-cyan-600 border-t-transparent" />
      </div>
    );
  }

  const catIri = defaultCat ? iri(defaultCat, "pricing-categories") : "";

  return (
    <div className="font-sans">
      {/* Hero */}
      <section className="bg-gradient-to-b from-gray-950 to-gray-900 pb-20 pt-24">
        <div className="mx-auto max-w-4xl px-6 text-center">
          <p className="mb-4 text-sm font-medium uppercase tracking-widest text-cyan-500">Tarification</p>
          <h1 className="text-4xl font-bold text-white md:text-5xl lg:text-6xl">
            Simple, transparent, adapté à votre activité
          </h1>
          <p className="mx-auto mt-6 max-w-2xl text-lg leading-relaxed text-gray-400">
            Deux offres claires. Des modules complémentaires à la carte.
            Payez uniquement ce dont vous avez besoin.
          </p>
        </div>
      </section>

      {/* Tier cards */}
      <section className="bg-white py-20">
        <div className="mx-auto max-w-5xl px-6">
          <div className="mx-auto max-w-3xl text-center mb-14">
            <h2 className="text-3xl font-bold text-gray-900 md:text-4xl">Choisissez votre offre</h2>
            <p className="mt-4 text-base text-gray-500">
              Tarif dégressif selon la taille de votre flotte. L&apos;offre Premium inclut toutes les fonctionnalités de l&apos;offre Essentiel.
            </p>
          </div>

          <div className="grid gap-8 md:grid-cols-2">
            {TIER_ORDER.map((tierKey) => {
              const meta = TIER_META[tierKey];
              const tierPacks = packsByTier[tierKey] || [];
              const pricing = catIri ? getTierPricing(tierKey, catIri) : [];
              const startPrice = pricing[0]?.pricePerAeronef;
              const isPopular = tierKey === "premium";

              return (
                <div
                  key={tierKey}
                  className={`relative flex flex-col rounded-2xl border-2 p-8 transition-all hover:shadow-lg ${
                    isPopular ? `${meta.border} shadow-md` : "border-gray-200"
                  }`}
                >
                  {isPopular && (
                    <span className="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-purple-600 px-4 py-1 text-xs font-bold uppercase tracking-wide text-white">
                      Recommandé pour les pros
                    </span>
                  )}

                  <div className="text-4xl mb-3">{meta.icon}</div>
                  <h3 className={`text-2xl font-bold ${meta.color}`}>{meta.label}</h3>
                  <p className="mt-1 text-sm text-gray-500">{meta.tagline}</p>

                  <div className="my-6">
                    <div className="flex items-baseline gap-1">
                      <span className="text-5xl font-bold text-gray-900">{startPrice ?? "—"}</span>
                      <span className="text-sm text-gray-400">€/aéronef/mois</span>
                    </div>
                    {pricing.length > 1 && (
                      <p className="text-xs text-gray-400 mt-1">
                        à partir de {pricing[pricing.length - 1].pricePerAeronef}€ pour {pricing[pricing.length - 1].minAeronefs}+ machines
                      </p>
                    )}
                  </div>

                  <div className="h-px bg-gray-100 my-3" />

                  <div className="flex-1 space-y-3">
                    {tierKey === "premium" && (
                      <p className="text-xs font-semibold text-gray-400 uppercase mb-2">
                        Tout Essentiel +
                      </p>
                    )}
                    {tierPacks.map((pack) => (
                      <div key={pack.id}>
                        <p className="text-sm font-semibold text-gray-700">{pack.name}</p>
                        {pack.featuresList && (
                          <ul className="mt-0.5 space-y-0.5">
                            {pack.featuresList.split(",").map((f, i) => (
                              <li key={i} className="flex items-start gap-1.5 text-xs text-gray-500">
                                <span className="text-green-500 mt-0.5 flex-shrink-0">✓</span>
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
                    className={`mt-8 block w-full rounded-xl py-3.5 text-center text-sm font-bold transition ${
                      isPopular
                        ? "bg-purple-600 text-white hover:bg-purple-700"
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

      {/* Grille tarifaire */}
      <section className="bg-gray-50 py-20">
        <div className="mx-auto max-w-4xl px-6">
          <div className="mx-auto max-w-2xl text-center mb-14">
            <h2 className="text-3xl font-bold text-gray-900 md:text-4xl">Grille tarifaire</h2>
            <p className="mt-4 text-base text-gray-500">
              Prix par aéronef et par mois, dégressif selon la taille de votre flotte.
            </p>
          </div>

          <div className="overflow-hidden rounded-xl border border-gray-200 shadow-sm">
            <table className="w-full">
              <thead>
                <tr className="bg-gray-900 text-white">
                  <th className="px-6 py-4 text-left text-sm font-semibold">Flotte</th>
                  {TIER_ORDER.map((tk) => (
                    <th key={tk} className="px-6 py-4 text-center text-sm font-semibold">
                      {TIER_META[tk].icon} {TIER_META[tk].label}
                    </th>
                  ))}
                </tr>
              </thead>
              <tbody>
                {catIri && getTierPricing("essentiel", catIri).map((bracket, idx) => (
                  <tr key={idx} className={idx % 2 === 0 ? "bg-white" : "bg-gray-50"}>
                    <td className="border-t border-gray-100 px-6 py-4 font-medium text-gray-700 text-sm">
                      {bracket.maxAeronefs
                        ? `${bracket.minAeronefs} à ${bracket.maxAeronefs}`
                        : `${bracket.minAeronefs}+`} aéronefs
                    </td>
                    {TIER_ORDER.map((tk) => {
                      const t = getAeronefPrice(tk, catIri, bracket.minAeronefs);
                      return (
                        <td key={tk} className="border-t border-gray-100 px-6 py-4 text-center">
                          {t ? (
                            <span className="text-xl font-bold text-gray-900">{t.pricePerAeronef}<span className="text-xs font-normal text-gray-400"> €</span></span>
                          ) : "—"}
                        </td>
                      );
                    })}
                  </tr>
                ))}
                {defaultCat?.maintenanceDiscount && (
                  <tr className="bg-amber-50">
                    <td className="border-t border-amber-200 px-6 py-4 font-medium text-amber-800 text-sm">
                      Aéronef en maintenance
                    </td>
                    {TIER_ORDER.map((tk) => (
                      <td key={tk} className="border-t border-amber-200 px-6 py-4 text-center">
                        <span className="rounded-full bg-amber-200 px-3 py-0.5 text-xs font-semibold text-amber-800">
                          -{defaultCat.maintenanceDiscount}%
                        </span>
                      </td>
                    ))}
                  </tr>
                )}
              </tbody>
            </table>
          </div>

          {categories.length > 1 && (
            <p className="mt-4 text-center text-sm text-gray-400">
              Grille affichée : {defaultCat?.name}.
              {categories.filter(c => c.id !== defaultCat?.id).map(c => (
                <span key={c.id}> {c.name}{c.discountPercent ? ` (-${c.discountPercent}%)` : ""}</span>
              ))}{" "}également disponible{categories.length > 2 ? "s" : ""}.
            </p>
          )}
        </div>
      </section>

      {/* Modules add-on */}
      {addonPacks.length > 0 && (
        <section className="bg-white py-20">
          <div className="mx-auto max-w-5xl px-6">
            <div className="mx-auto max-w-2xl text-center mb-14">
              <h2 className="text-3xl font-bold text-gray-900 md:text-4xl">Modules à la carte</h2>
              <p className="mt-4 text-base text-gray-500">
                Complétez votre offre avec les modules dont vous avez besoin.
                Accessibles depuis n&apos;importe quelle offre, en forfait mensuel fixe.
              </p>
            </div>

            <div className="grid gap-5 md:grid-cols-2">
              {addonPacks.map((pack) => {
                const price = catIri ? getAddonPrice(iri(pack, "module-packs"), catIri) : null;
                return (
                  <div key={pack.id} className="rounded-xl border border-gray-200 p-6 hover:shadow-md transition-shadow">
                    <div className="flex items-start justify-between">
                      <div>
                        <h3 className="text-lg font-bold text-gray-900">{pack.name}</h3>
                        {pack.description && <p className="mt-1 text-sm text-gray-500">{pack.description}</p>}
                      </div>
                      <div className="text-right flex-shrink-0 ml-4">
                        <span className="text-2xl font-bold text-gray-900">{price?.monthlyPrice ?? "—"}</span>
                        <span className="text-xs text-gray-400"> €/mois</span>
                      </div>
                    </div>
                    {pack.featuresList && (
                      <ul className="mt-4 space-y-1.5">
                        {pack.featuresList.split(",").map((f, i) => (
                          <li key={i} className="flex items-center gap-2 text-sm text-gray-600">
                            <span className="text-green-500">✓</span>{f.trim()}
                          </li>
                        ))}
                      </ul>
                    )}
                  </div>
                );
              })}
            </div>
          </div>
        </section>
      )}

      {/* Simulateur */}
      <section className="bg-gray-50 py-20">
        <div className="mx-auto max-w-4xl px-6">
          <div className="mx-auto max-w-2xl text-center mb-10">
            <h2 className="text-3xl font-bold text-gray-900 md:text-4xl">Simulateur de coût</h2>
            <p className="mt-4 text-base text-gray-500">
              Estimez votre budget mensuel en quelques clics.
            </p>
          </div>

          <div className="rounded-2xl border border-gray-200 bg-white p-8 shadow-sm">
            <div className="grid gap-6 md:grid-cols-2">
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

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Offre</label>
                <select
                  value={simTier}
                  onChange={(e) => { setSimTier(e.target.value); }}
                  className="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm"
                >
                  {TIER_ORDER.map((t) => (
                    <option key={t} value={t}>{TIER_META[t].icon} {TIER_META[t].label}</option>
                  ))}
                </select>
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Aéronefs actifs : <span className="font-bold text-cyan-700">{simAeronefs}</span>
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

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  En maintenance : <span className="font-bold text-amber-700">{simMaintenance}</span>
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
            </div>

            {/* Add-ons */}
            {addonPacks.length > 0 && (
              <div className="mt-6 pt-6 border-t border-gray-100">
                <p className="text-sm font-medium text-gray-700 mb-3">Modules complémentaires :</p>
                <div className="flex flex-wrap gap-2">
                  {addonPacks.map((pack) => {
                    const pp = simCategory ? getAddonPrice(iri(pack, "module-packs"), simCategory) : null;
                    const isSelected = simAddons.includes(pack.slug);
                    return (
                      <button
                        key={pack.id}
                        onClick={() => toggleAddon(pack.slug)}
                        className={`rounded-lg px-3 py-2 text-xs font-medium transition border ${
                          isSelected
                            ? "border-cyan-500 bg-cyan-50 text-cyan-700"
                            : "border-gray-200 bg-white text-gray-600 hover:bg-gray-50"
                        }`}
                      >
                        {pack.name} (+{pp?.monthlyPrice ?? 0}€)
                      </button>
                    );
                  })}
                </div>
              </div>
            )}

            {/* Résultat */}
            {simResult && (
              <div className="mt-8 space-y-3">
                <div className="flex items-center justify-between rounded-lg bg-gray-50 px-4 py-3">
                  <span className="text-sm text-gray-700">
                    {simAeronefs} aéronef{simAeronefs > 1 ? "s" : ""} × {simResult.tier.pricePerAeronef} €
                    <span className="text-gray-400"> (offre {TIER_META[simTier].label})</span>
                  </span>
                  <span className="font-semibold text-gray-900">{simResult.activeAeronefCost.toFixed(2)} €</span>
                </div>

                {simMaintenance > 0 && (
                  <div className="flex items-center justify-between rounded-lg bg-amber-50 px-4 py-3">
                    <span className="text-sm text-amber-800">
                      {simMaintenance} en maintenance × {simResult.tier.pricePerAeronef} € (-{simResult.maintenanceDiscount}%)
                    </span>
                    <span className="font-semibold text-amber-800">{simResult.maintenanceCost.toFixed(2)} €</span>
                  </div>
                )}

                {simResult.addonDetails.map((a) => (
                  <div key={a.name} className="flex items-center justify-between rounded-lg bg-cyan-50 px-4 py-3">
                    <span className="text-sm text-cyan-800">Module {a.name}</span>
                    <span className="font-semibold text-cyan-800">{a.price.toFixed(2)} €</span>
                  </div>
                ))}

                <div className="h-px bg-gray-200" />

                <div className="flex items-center justify-between px-4 py-4 rounded-lg bg-gradient-to-r from-cyan-50 to-blue-50 border border-cyan-100">
                  <span className="text-base font-bold text-gray-900">Total mensuel estimé</span>
                  <span className="text-3xl font-bold text-cyan-700">
                    {simResult.total.toFixed(2).replace(".", ",")} €
                    <span className="text-sm font-normal text-gray-500"> /mois</span>
                  </span>
                </div>

                <div className="rounded-lg bg-green-50 px-4 py-3 text-center border border-green-100">
                  <p className="text-sm font-medium text-green-700">
                    Essai gratuit 30 jours — sans carte bancaire — sans engagement
                  </p>
                </div>
              </div>
            )}
          </div>
        </div>
      </section>

      {/* Lien fonctionnalités */}
      <section className="bg-white py-16">
        <div className="mx-auto max-w-4xl px-6 text-center">
          <h2 className="text-2xl font-bold text-gray-900 md:text-3xl">Besoin de plus de détails ?</h2>
          <p className="mt-4 text-base text-gray-500">
            Découvrez en détail chaque fonctionnalité et comment elles transforment votre activité.
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
