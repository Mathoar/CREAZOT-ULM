"use client";

import { useEffect, useState } from "react";
import {
  Card,
  CardContent,
  Checkbox,
  Chip,
  CircularProgress,
  Alert,
  ToggleButton,
  ToggleButtonGroup,
} from "@mui/material";
import type { RegistrationData } from "./RegisterStepper";

interface ModulePack {
  id: number;
  "@id"?: string;
  name: string;
  slug: string;
  description?: string;
  isDefault?: boolean;
  tierGroup?: string;
  featuresList?: string;
  addonFrom?: string;
  isAddon?: boolean;
}

interface ModulePackPrice {
  id: number;
  modulePack: string;
  pricingCategory: string;
  monthlyPrice: number;
}

interface PricingCategory {
  id: number;
  "@id"?: string;
  name: string;
  slug?: string;
  isDefault?: boolean;
  discountPercent?: number;
}

interface PricingTier {
  id: number;
  pricingCategory: string;
  tierGroup?: string;
  minAeronefs: number;
  maxAeronefs: number | null;
  pricePerAeronef: number;
}

interface StepModulesProps {
  selectedPackIds: number[];
  nbAeronefs: number;
  tier: string;
  categorySlug: string;
  onChange: (values: Partial<RegistrationData["modules"]>) => void;
}

const TIER_META: Record<string, { label: string; tagline: string; icon: string }> = {
  essentiel: {
    label: "Essentiel",
    tagline: "Conformité réglementaire & opérationnel",
    icon: "🛡️",
  },
  premium: {
    label: "Premium",
    tagline: "Gestion commerciale complète + Briefing météo IA",
    icon: "🚀",
  },
};

const iri = (e: { "@id"?: string; id: number }, r: string) => e["@id"] || `/${r}/${e.id}`;

export default function StepModules({
  selectedPackIds,
  nbAeronefs,
  tier,
  categorySlug,
  onChange,
}: StepModulesProps) {
  const [packs, setPacks] = useState<ModulePack[]>([]);
  const [prices, setPrices] = useState<ModulePackPrice[]>([]);
  const [categories, setCategories] = useState<PricingCategory[]>([]);
  const [tiers, setTiers] = useState<PricingTier[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const base = window.origin;
    const safeFetch = (url: string) =>
      fetch(url, { headers: { Accept: "application/ld+json" } }).then((r) => {
        if (!r.ok) throw new Error(`${r.status}`);
        return r.json();
      });

    Promise.all([
      safeFetch(`${base}/module-packs?pagination=false`),
      safeFetch(`${base}/module-pack-prices?pagination=false`),
      safeFetch(`${base}/pricing-categories?pagination=false`),
      safeFetch(`${base}/pricing-tiers?pagination=false`),
    ])
      .then(([packsRes, pricesRes, categoriesRes, tiersRes]) => {
        const toArray = <T,>(res: any): T[] => {
          const data = res?.["hydra:member"] ?? res;
          return Array.isArray(data) ? data : [];
        };

        const allPacks = toArray<ModulePack>(packsRes).filter((p) => p.tierGroup !== "hidden");
        const allTiers = toArray<PricingTier>(tiersRes).filter(
          (t) => t.tierGroup && !t.tierGroup.startsWith("legacy")
        );
        const activeCatIris = new Set(allTiers.map((t) => t.pricingCategory));
        const allCats = toArray<PricingCategory>(categoriesRes).filter(
          (c) => activeCatIris.has(iri(c, "pricing-categories"))
        );

        setPacks(allPacks);
        setPrices(toArray<ModulePackPrice>(pricesRes));
        setCategories(allCats);
        setTiers(allTiers);

        if (!categorySlug && allCats.length > 0) {
          const def = allCats.find((c) => c.isDefault) || allCats[0];
          if (def) onChange({ categorySlug: def.slug || "" });
        }
      })
      .catch(() => {
        setError("Données de tarification indisponibles");
      })
      .finally(() => setLoading(false));
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const activeCategory =
    categories.find((c) => c.slug === categorySlug) ||
    categories.find((c) => c.isDefault) ||
    categories[0];

  const catIri = activeCategory ? iri(activeCategory, "pricing-categories") : "";

  const getAeronefPrice = (): number => {
    if (!catIri) return 0;
    const t = tiers.find(
      (t) =>
        t.pricingCategory === catIri &&
        t.tierGroup === tier &&
        nbAeronefs >= t.minAeronefs &&
        (t.maxAeronefs == null || nbAeronefs <= t.maxAeronefs)
    );
    return t ? t.pricePerAeronef : 0;
  };

  const getPackPrice = (pack: ModulePack): number => {
    if (!catIri) return 0;
    const packIri = iri(pack, "module-packs");
    const mp = prices.find((p) => p.modulePack === packIri && p.pricingCategory === catIri);
    return mp ? mp.monthlyPrice : 0;
  };

  const isIncludedInTier = (pack: ModulePack): boolean => {
    return tier === "premium" && pack.addonFrom === "essentiel";
  };

  const addonPacks = packs.filter((p) => !!p.addonFrom);

  const toggleAddon = (packId: number) => {
    const pack = packs.find((p) => p.id === packId);
    if (!pack || isIncludedInTier(pack)) return;
    const ids = selectedPackIds.includes(packId)
      ? selectedPackIds.filter((id) => id !== packId)
      : [...selectedPackIds, packId];
    onChange({ packIds: ids });
  };

  const aeronefPrice = getAeronefPrice();
  const aeronefTotal = nbAeronefs * aeronefPrice;
  const addonsTotal = selectedPackIds.reduce((sum, id) => {
    const pack = packs.find((p) => p.id === id);
    if (!pack || isIncludedInTier(pack)) return sum;
    return sum + getPackPrice(pack);
  }, 0);
  const grandTotal = aeronefTotal + addonsTotal;

  if (loading) {
    return (
      <div className="flex items-center justify-center py-12">
        <CircularProgress sx={{ color: "#0f929a" }} />
      </div>
    );
  }

  return (
    <div className="space-y-6">
      {error && <Alert severity="warning">{error}</Alert>}

      {/* Catégorie tarifaire */}
      {categories.length > 1 && (
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">
            Type de structure
          </label>
          <div className="flex gap-2">
            {categories.map((cat) => (
              <button
                key={cat.id}
                type="button"
                onClick={() => onChange({ categorySlug: cat.slug || "" })}
                className={`rounded-lg px-4 py-2 text-sm font-medium border transition ${
                  categorySlug === cat.slug
                    ? "border-cyan-500 bg-cyan-50 text-cyan-700"
                    : "border-gray-200 bg-white text-gray-600 hover:bg-gray-50"
                }`}
              >
                {cat.name}
                {cat.discountPercent ? ` (-${cat.discountPercent}%)` : ""}
              </button>
            ))}
          </div>
        </div>
      )}

      {/* Choix du tier */}
      <div>
        <h2 className="text-base font-semibold text-black mb-3">Choisissez votre offre</h2>
        <div className="grid gap-3 md:grid-cols-2">
          {(["essentiel", "premium"] as const).map((t) => {
            const meta = TIER_META[t];
            const isSelected = tier === t;
            const price = tiers.find(
              (pt) =>
                pt.pricingCategory === catIri &&
                pt.tierGroup === t &&
                nbAeronefs >= pt.minAeronefs &&
                (pt.maxAeronefs == null || nbAeronefs <= pt.maxAeronefs)
            );
            return (
              <Card
                key={t}
                onClick={() => onChange({ tier: t })}
                sx={{
                  cursor: "pointer",
                  borderLeft: isSelected ? "4px solid" : "4px solid transparent",
                  borderLeftColor: isSelected ? (t === "premium" ? "#9333ea" : "#0f929a") : "transparent",
                  bgcolor: isSelected ? (t === "premium" ? "#faf5ff" : "#f0fdfa") : "#fff",
                  transition: "all 0.2s",
                  "&:hover": { boxShadow: 3 },
                }}
              >
                <CardContent sx={{ py: 2 }}>
                  <div className="flex items-center justify-between">
                    <div>
                      <span className="text-xl mr-2">{meta.icon}</span>
                      <span className="font-bold text-black">{meta.label}</span>
                      <p className="mt-0.5 text-xs text-gray-500">{meta.tagline}</p>
                    </div>
                    <div className="text-right">
                      <span className="text-xl font-bold text-black">
                        {price?.pricePerAeronef ?? "—"}€
                      </span>
                      <p className="text-xs text-gray-400">/aéronef/mois</p>
                    </div>
                  </div>
                </CardContent>
              </Card>
            );
          })}
        </div>
      </div>

      {/* Add-ons */}
      {addonPacks.length > 0 && (
        <div>
          <h2 className="text-base font-semibold text-black mb-3">Modules complémentaires</h2>
          <div className="space-y-2">
            {addonPacks.map((pack) => {
              const included = isIncludedInTier(pack);
              const isSelected = included || selectedPackIds.includes(pack.id);
              const price = getPackPrice(pack);

              return (
                <Card
                  key={pack.id}
                  onClick={() => toggleAddon(pack.id)}
                  sx={{
                    cursor: included ? "default" : "pointer",
                    borderLeft: isSelected ? "4px solid" : "4px solid transparent",
                    borderLeftColor: isSelected ? (included ? "#9333ea" : "#0f929a") : "transparent",
                    bgcolor: included ? "#faf5ff" : isSelected ? "#f0fdfa" : "#fff",
                    transition: "all 0.2s",
                    "&:hover": included ? {} : { boxShadow: 3 },
                  }}
                >
                  <CardContent sx={{ display: "flex", alignItems: "center", gap: 2, py: 1.5 }}>
                    <Checkbox
                      checked={isSelected}
                      disabled={included}
                      sx={{
                        color: included ? "#9333ea" : "#0f929a",
                        "&.Mui-checked": { color: included ? "#9333ea" : "#0f929a" },
                        p: 0,
                      }}
                    />
                    <div className="flex-1">
                      <div className="flex items-center gap-2">
                        <span className="font-semibold text-black">{pack.name}</span>
                        {included && (
                          <Chip
                            label="INCLUS DANS PREMIUM"
                            size="small"
                            sx={{
                              bgcolor: "#9333ea",
                              color: "#fff",
                              fontWeight: 600,
                              fontSize: "0.65rem",
                              height: 20,
                            }}
                          />
                        )}
                      </div>
                      {pack.featuresList && (
                        <p className="mt-0.5 text-xs text-gray-500">
                          {pack.featuresList.split(",").map((f) => f.trim()).join(" · ")}
                        </p>
                      )}
                    </div>
                    <div className="text-right">
                      {included ? (
                        <span className="text-sm font-medium text-purple-600">Inclus</span>
                      ) : (
                        <span className="text-sm font-semibold text-black">
                          {price}€<span className="text-xs text-gray-400">/mois</span>
                        </span>
                      )}
                    </div>
                  </CardContent>
                </Card>
              );
            })}
          </div>
        </div>
      )}

      {/* Récapitulatif */}
      <div className="rounded-lg bg-cyan-200/30 p-5">
        <h3 className="mb-3 text-sm font-semibold text-black">Récapitulatif estimé</h3>
        <div className="space-y-2 text-sm">
          <div className="flex justify-between">
            <span className="text-gray-600">
              {TIER_META[tier].icon} {TIER_META[tier].label} — {nbAeronefs} aéronef{nbAeronefs > 1 ? "s" : ""} × {aeronefPrice}€
            </span>
            <span className="font-medium text-black">{aeronefTotal.toFixed(2)} €</span>
          </div>
          {selectedPackIds.map((id) => {
            const pack = packs.find((p) => p.id === id);
            if (!pack) return null;
            const included = isIncludedInTier(pack);
            const price = included ? 0 : getPackPrice(pack);
            return (
              <div key={id} className="flex justify-between">
                <span className={included ? "text-purple-600" : "text-gray-600"}>
                  {pack.name}{included ? " (inclus)" : ""}
                </span>
                <span className={`font-medium ${included ? "text-purple-600" : "text-black"}`}>
                  {price.toFixed(2)} €
                </span>
              </div>
            );
          })}
          <div className="border-t border-gray-300 pt-2">
            <div className="flex items-center justify-between">
              <span className="font-semibold text-black">Total estimé / mois</span>
              <span className="text-lg font-bold text-cyan-700">
                {grandTotal.toFixed(2)} €
              </span>
            </div>
          </div>
        </div>
        <div className="mt-3">
          <Chip
            label="Gratuit pendant 30 jours"
            sx={{
              bgcolor: "#d1fae5",
              color: "#065f46",
              fontWeight: 600,
              fontSize: "0.8rem",
            }}
          />
        </div>
      </div>
    </div>
  );
}
