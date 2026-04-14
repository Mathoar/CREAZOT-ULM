import { useEffect, useState } from "react";
import { useDataProvider, useRedirect, Title, CreateButton, TopToolbar } from "react-admin";
import {
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Paper,
  Chip,
  Box,
  Typography,
  IconButton,
  Tooltip,
  Skeleton,
} from "@mui/material";
import EditIcon from "@mui/icons-material/Edit";
import FlightIcon from "@mui/icons-material/Flight";

interface PricingCategory {
  id: string | number;
  "@id"?: string;
  name: string;
  slug: string;
  discountPercent?: number;
  maintenanceDiscount?: number;
  isActive?: boolean;
  isDefault?: boolean;
}

interface PricingTier {
  id: string | number;
  "@id"?: string;
  pricingCategory: string;
  minAeronefs: number;
  maxAeronefs: number | null;
  pricePerAeronef: number;
}

const BRAND_COLORS: Record<string, { bg: string; head: string; accent: string }> = {
  ffplum: { bg: "#f1f8e9", head: "#c8e6c9", accent: "#2e7d32" },
};
const DEFAULT_COLORS = { bg: "#fafafa", head: "#e3f2fd", accent: "#1565c0" };
const catColors = (slug: string) => BRAND_COLORS[slug] || DEFAULT_COLORS;

const catIri = (cat: { "@id"?: string; id: string | number }) =>
  cat["@id"] || `/pricing-categories/${cat.id}`;

export const PricingTiersList = () => {
  const dataProvider = useDataProvider();
  const redirect = useRedirect();

  const [categories, setCategories] = useState<PricingCategory[]>([]);
  const [tiers, setTiers] = useState<PricingTier[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    Promise.all([
      dataProvider.getList("pricing-categories", {
        pagination: { page: 1, perPage: 50 },
        sort: { field: "name", order: "ASC" },
        filter: {},
      }),
      dataProvider.getList("pricing-tiers", {
        pagination: { page: 1, perPage: 100 },
        sort: { field: "minAeronefs", order: "ASC" },
        filter: {},
      }),
    ]).then(([catRes, tierRes]) => {
      setCategories(catRes.data);
      setTiers(tierRes.data);
      setLoading(false);
    });
  }, [dataProvider]);

  const getTiers = (cIri: string) =>
    tiers.filter((t) => t.pricingCategory === cIri).sort((a, b) => a.minAeronefs - b.minAeronefs);

  if (loading) {
    return (
      <Box sx={{ p: 3 }}>
        <Skeleton variant="rectangular" height={350} sx={{ borderRadius: 2 }} />
      </Box>
    );
  }

  const maxTierCount = Math.max(...categories.map((c) => getTiers(catIri(c)).length), 0);

  return (
    <Box sx={{ width: "100%", px: { xs: 1, md: 3 }, py: 2 }}>
      <Title title="Paliers tarifaires" />

      <Box sx={{ display: "flex", alignItems: "center", justifyContent: "space-between", mb: 3 }}>
        <Box>
          <Typography variant="h5" fontWeight={700} color="text.primary">
            Paliers tarifaires
          </Typography>
          <Typography variant="body2" color="text.secondary">
            Tarif par aéronef selon le nombre d&apos;appareils et la grille tarifaire
          </Typography>
        </Box>
        <TopToolbar sx={{ p: 0, minHeight: "auto" }}>
          <CreateButton resource="pricing-tiers" label="Ajouter un palier" />
        </TopToolbar>
      </Box>

      <Paper elevation={1} sx={{ borderRadius: 2, overflow: "hidden" }}>
        {/* ── Bandeau ── */}
        <Box sx={{ px: 2.5, py: 1.5, backgroundColor: "#263238", display: "flex", alignItems: "center", gap: 1 }}>
          <FlightIcon sx={{ color: "#fff", fontSize: 20 }} />
          <Typography variant="subtitle1" fontWeight={600} color="#fff">
            Prix par aéronef / mois
          </Typography>
        </Box>

        <TableContainer>
          <Table>
            {/* ── En-têtes grilles ── */}
            <TableHead>
              <TableRow sx={{ backgroundColor: "#f5f5f5" }}>
                <TableCell
                  sx={{
                    fontWeight: 700,
                    minWidth: 220,
                    backgroundColor: "#f5f5f5",
                    borderRight: "2px solid #e0e0e0",
                    py: 2,
                  }}
                >
                  <Typography variant="subtitle2" fontWeight={700}>
                    Nombre d&apos;aéronefs
                  </Typography>
                </TableCell>
                {categories.map((cat) => {
                  const c = catColors(cat.slug);
                  return (
                    <TableCell
                      key={cat.id}
                      align="center"
                      sx={{
                        fontWeight: 700,
                        minWidth: 180,
                        backgroundColor: c.head,
                        borderRight: "1px solid #e0e0e0",
                        py: 2,
                      }}
                    >
                      <Typography variant="subtitle1" fontWeight={700}>
                        {cat.name}
                      </Typography>
                      <Box sx={{ display: "flex", gap: 0.5, justifyContent: "center", mt: 0.5, flexWrap: "wrap" }}>
                        {cat.discountPercent ? (
                          <Chip
                            label={`-${cat.discountPercent}% sur tarif public`}
                            size="small"
                            sx={{ backgroundColor: c.accent, color: "#fff", fontWeight: 600, height: 22, fontSize: "0.72rem" }}
                          />
                        ) : null}
                        {cat.isDefault ? (
                          <Chip label="Par défaut" size="small" variant="outlined" sx={{ height: 22, fontSize: "0.72rem" }} />
                        ) : null}
                      </Box>
                    </TableCell>
                  );
                })}
              </TableRow>
            </TableHead>

            <TableBody>
              {/* ── Lignes paliers ── */}
              {Array.from({ length: maxTierCount }).map((_, tierIdx) => {
                const firstCat = categories[0];
                const refTier = firstCat ? getTiers(catIri(firstCat))[tierIdx] : null;

                return (
                  <TableRow
                    key={tierIdx}
                    sx={{
                      backgroundColor: tierIdx % 2 === 0 ? "#fff" : "#fafafa",
                      "&:hover": { backgroundColor: "#f0f7ff" },
                      transition: "background-color 0.15s",
                    }}
                  >
                    <TableCell sx={{ borderRight: "2px solid #e0e0e0", py: 2 }}>
                      {refTier && (
                        <Box>
                          <Typography variant="body1" fontWeight={600}>
                            {refTier.maxAeronefs
                              ? `${refTier.minAeronefs} à ${refTier.maxAeronefs}`
                              : `${refTier.minAeronefs}+`}{" "}
                            aéronefs
                          </Typography>
                          <Typography variant="caption" color="text.secondary">
                            {refTier.maxAeronefs
                              ? `de ${refTier.minAeronefs} à ${refTier.maxAeronefs} appareils`
                              : `à partir de ${refTier.minAeronefs} appareils`}
                          </Typography>
                        </Box>
                      )}
                    </TableCell>
                    {categories.map((cat) => {
                      const c = catColors(cat.slug);
                      const tier = getTiers(catIri(cat))[tierIdx];
                      return (
                        <TableCell
                          key={cat.id}
                          align="center"
                          sx={{
                            borderRight: "1px solid #e0e0e0",
                            backgroundColor: BRAND_COLORS[cat.slug] ? c.bg : undefined,
                            py: 2,
                          }}
                        >
                          {tier ? (
                            <Box sx={{ display: "flex", alignItems: "center", justifyContent: "center", gap: 1 }}>
                              <Typography variant="h5" fontWeight={700} color={c.accent}>
                                {tier.pricePerAeronef.toFixed(0)} €
                              </Typography>
                              <Typography variant="caption" color="text.secondary">
                                /aéronef
                                <br />
                                /mois
                              </Typography>
                              <Tooltip title="Modifier ce palier">
                                <IconButton
                                  size="small"
                                  onClick={() => redirect("edit", "pricing-tiers", tier.id)}
                                  sx={{ opacity: 0.3, "&:hover": { opacity: 1 } }}
                                >
                                  <EditIcon sx={{ fontSize: 16 }} />
                                </IconButton>
                              </Tooltip>
                            </Box>
                          ) : (
                            <Typography color="text.disabled">—</Typography>
                          )}
                        </TableCell>
                      );
                    })}
                  </TableRow>
                );
              })}

              {/* ── Ligne maintenance ── */}
              {categories.some((c) => c.maintenanceDiscount) && (
                <TableRow sx={{ backgroundColor: "#fff8e1" }}>
                  <TableCell sx={{ borderRight: "2px solid #e0e0e0", py: 1.5 }}>
                    <Typography variant="body2" fontWeight={600}>
                      Aéronef en maintenance
                    </Typography>
                    <Typography variant="caption" color="text.secondary">
                      isAvailable = false
                    </Typography>
                  </TableCell>
                  {categories.map((cat) => (
                    <TableCell key={cat.id} align="center" sx={{ borderRight: "1px solid #e0e0e0", py: 1.5 }}>
                      {cat.maintenanceDiscount ? (
                        <Chip
                          label={`-${cat.maintenanceDiscount}% sur le palier`}
                          size="small"
                          color="warning"
                          variant="outlined"
                          sx={{ fontWeight: 600, fontSize: "0.78rem" }}
                        />
                      ) : (
                        <Typography color="text.disabled">—</Typography>
                      )}
                    </TableCell>
                  ))}
                </TableRow>
              )}

              {/* ── Ligne exemple simulé ── */}
              <TableRow sx={{ backgroundColor: "#e8eaf6" }}>
                <TableCell sx={{ borderRight: "2px solid #e0e0e0", py: 1.5 }}>
                  <Typography variant="body2" fontWeight={600} color="#283593">
                    Exemple : 4 aéronefs
                  </Typography>
                  <Typography variant="caption" color="text.secondary">
                    dont 1 en maintenance
                  </Typography>
                </TableCell>
                {categories.map((cat) => {
                  const c = catColors(cat.slug);
                  const tierList = getTiers(catIri(cat));
                  const tier = tierList.find((t) => 4 >= t.minAeronefs && (t.maxAeronefs === null || 4 <= t.maxAeronefs));
                  if (!tier) return <TableCell key={cat.id} align="center" sx={{ borderRight: "1px solid #e0e0e0" }}>—</TableCell>;

                  const discount = cat.maintenanceDiscount || 0;
                  const fullPrice = tier.pricePerAeronef * 3;
                  const maintenancePrice = tier.pricePerAeronef * (1 - discount / 100);
                  const total = fullPrice + maintenancePrice;

                  return (
                    <TableCell key={cat.id} align="center" sx={{ borderRight: "1px solid #e0e0e0", py: 1.5 }}>
                      <Typography variant="caption" color="text.secondary" display="block">
                        3 × {tier.pricePerAeronef.toFixed(0)}€ + 1 × {maintenancePrice.toFixed(0)}€
                      </Typography>
                      <Typography variant="h6" fontWeight={700} color={c.accent}>
                        {total.toFixed(0)} €/mois
                      </Typography>
                    </TableCell>
                  );
                })}
              </TableRow>
            </TableBody>
          </Table>
        </TableContainer>
      </Paper>

      {/* ── Légende ── */}
      <Box sx={{ mt: 2, display: "flex", gap: 4, color: "text.secondary", flexWrap: "wrap" }}>
        <Box sx={{ display: "flex", alignItems: "center", gap: 0.5 }}>
          <Chip label="-10%" size="small" color="warning" variant="outlined" sx={{ height: 18, fontSize: "0.65rem" }} />
          <Typography variant="caption">Remise appliquée aux aéronefs en maintenance</Typography>
        </Box>
        <Box sx={{ display: "flex", alignItems: "center", gap: 0.5 }}>
          <Box sx={{ width: 14, height: 14, backgroundColor: "#e8eaf6", borderRadius: 0.5, border: "1px solid #c5cae9" }} />
          <Typography variant="caption">Simulation de prix mensuel</Typography>
        </Box>
      </Box>
    </Box>
  );
};
