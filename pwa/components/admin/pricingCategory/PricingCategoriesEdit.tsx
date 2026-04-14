import { useEffect, useState, useCallback } from "react";
import {
  Edit,
  SimpleForm,
  TextInput,
  NumberInput,
  BooleanInput,
  DateTimeInput,
  required,
  useRecordContext,
  useDataProvider,
  useRedirect,
  Button,
} from "react-admin";
import { Link } from "react-router-dom";
import AddIcon from "@mui/icons-material/Add";
import EditIcon from "@mui/icons-material/Edit";
import FlightIcon from "@mui/icons-material/Flight";
import {
  Typography,
  Divider,
  Box,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Paper,
  Chip,
  IconButton,
  Tooltip,
  Skeleton,
} from "@mui/material";

const AddTierButton = () => {
  const record = useRecordContext();
  if (!record) return null;
  return (
    <Button
      component={Link}
      to={`/pricing-tiers/create?pricingCategory=${encodeURIComponent(record["@id"] || record.id)}`}
      label="Ajouter un palier"
      startIcon={<AddIcon />}
    />
  );
};

const PricingTiersTable = () => {
  const record = useRecordContext();
  const dataProvider = useDataProvider();
  const redirect = useRedirect();

  const [allCategories, setAllCategories] = useState<any[]>([]);
  const [allTiers, setAllTiers] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);

  const categoryIri = record?.["@id"] || (record ? `/pricing-categories/${record.id}` : null);

  const fetchData = useCallback(() => {
    if (!record) return;
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
      setAllCategories(catRes.data);
      setAllTiers(tierRes.data);
      setLoading(false);
    });
  }, [record, dataProvider]);

  useEffect(() => { fetchData(); }, [fetchData]);

  if (loading || !record) return <Skeleton variant="rectangular" height={200} sx={{ borderRadius: 2 }} />;

  const catIri = (c: any) => c["@id"] || `/pricing-categories/${c.id}`;
  const getTiers = (cIri: string) =>
    allTiers.filter((t: any) => t.pricingCategory === cIri).sort((a: any, b: any) => a.minAeronefs - b.minAeronefs);

  const currentCat = allCategories.find((c: any) => catIri(c) === categoryIri);
  const otherCats = allCategories.filter((c: any) => catIri(c) !== categoryIri);

  const currentTiers = getTiers(categoryIri!);
  const maxLen = Math.max(currentTiers.length, ...otherCats.map((c: any) => getTiers(catIri(c)).length));

  const ACCENT = "#1565c0";
  const BG = "#e3f2fd";

  return (
    <Paper elevation={1} sx={{ borderRadius: 2, overflow: "hidden", mt: 1 }}>
      <Box sx={{ px: 2.5, py: 1.5, backgroundColor: "#263238", display: "flex", alignItems: "center", gap: 1 }}>
        <FlightIcon sx={{ color: "#fff", fontSize: 20 }} />
        <Typography variant="subtitle1" fontWeight={600} color="#fff">
          Paliers de cette grille
        </Typography>
      </Box>

      <TableContainer>
        <Table size="small">
          <TableHead>
            <TableRow sx={{ backgroundColor: "#f5f5f5" }}>
              <TableCell
                sx={{ fontWeight: 700, minWidth: 200, borderRight: "2px solid #e0e0e0", py: 1.5 }}
              >
                <Typography variant="subtitle2" fontWeight={700}>
                  Nombre d&apos;aéronefs
                </Typography>
              </TableCell>

              {/* Current category highlighted */}
              <TableCell
                align="center"
                sx={{ fontWeight: 700, minWidth: 180, backgroundColor: BG, borderRight: "2px solid " + ACCENT, py: 1.5 }}
              >
                <Typography variant="subtitle1" fontWeight={700} color={ACCENT}>
                  {currentCat?.name || "—"}
                </Typography>
                {currentCat?.discountPercent ? (
                  <Chip
                    label={`-${currentCat.discountPercent}%`}
                    size="small"
                    sx={{ backgroundColor: ACCENT, color: "#fff", fontWeight: 600, height: 20, fontSize: "0.7rem", mt: 0.3 }}
                  />
                ) : null}
              </TableCell>

              {/* Other categories for comparison */}
              {otherCats.map((cat: any) => (
                <TableCell
                  key={cat.id}
                  align="center"
                  sx={{ fontWeight: 700, minWidth: 140, borderRight: "1px solid #e0e0e0", py: 1.5, opacity: 0.65 }}
                >
                  <Typography variant="body2" fontWeight={600}>
                    {cat.name}
                  </Typography>
                  {cat.discountPercent ? (
                    <Typography variant="caption" color="text.secondary">
                      -{cat.discountPercent}%
                    </Typography>
                  ) : null}
                </TableCell>
              ))}
            </TableRow>
          </TableHead>

          <TableBody>
            {Array.from({ length: maxLen }).map((_, idx) => {
              const tier = currentTiers[idx];
              return (
                <TableRow
                  key={idx}
                  sx={{
                    backgroundColor: idx % 2 === 0 ? "#fff" : "#fafafa",
                    "&:hover": { backgroundColor: "#f0f7ff" },
                  }}
                >
                  <TableCell sx={{ borderRight: "2px solid #e0e0e0", py: 1.5 }}>
                    {tier ? (
                      <Typography variant="body2" fontWeight={600}>
                        {tier.maxAeronefs
                          ? `${tier.minAeronefs} à ${tier.maxAeronefs}`
                          : `${tier.minAeronefs}+`}{" "}
                        aéronefs
                      </Typography>
                    ) : (
                      <Typography variant="body2" color="text.disabled">—</Typography>
                    )}
                  </TableCell>

                  {/* Current cat — highlighted cells */}
                  <TableCell
                    align="center"
                    sx={{ borderRight: "2px solid " + ACCENT, backgroundColor: BG, py: 1.5 }}
                  >
                    {tier ? (
                      <Box sx={{ display: "flex", alignItems: "center", justifyContent: "center", gap: 0.5 }}>
                        <Typography variant="h6" fontWeight={700} color={ACCENT}>
                          {tier.pricePerAeronef.toFixed(0)} €
                        </Typography>
                        <Typography variant="caption" color="text.secondary">/mois</Typography>
                        <Tooltip title="Modifier">
                          <IconButton
                            size="small"
                            onClick={() => redirect("edit", "pricing-tiers", tier.id)}
                            sx={{ opacity: 0.3, "&:hover": { opacity: 1 }, ml: 0.5 }}
                          >
                            <EditIcon sx={{ fontSize: 15 }} />
                          </IconButton>
                        </Tooltip>
                      </Box>
                    ) : (
                      <Typography color="text.disabled">—</Typography>
                    )}
                  </TableCell>

                  {/* Other cats — dimmed for comparison */}
                  {otherCats.map((cat: any) => {
                    const t = getTiers(catIri(cat))[idx];
                    return (
                      <TableCell
                        key={cat.id}
                        align="center"
                        sx={{ borderRight: "1px solid #e0e0e0", py: 1.5, opacity: 0.55 }}
                      >
                        {t ? (
                          <Typography variant="body2">
                            {t.pricePerAeronef.toFixed(0)} €
                          </Typography>
                        ) : (
                          <Typography color="text.disabled">—</Typography>
                        )}
                      </TableCell>
                    );
                  })}
                </TableRow>
              );
            })}

            {/* Maintenance discount row */}
            {(currentCat?.maintenanceDiscount || otherCats.some((c: any) => c.maintenanceDiscount)) && (
              <TableRow sx={{ backgroundColor: "#fff8e1" }}>
                <TableCell sx={{ borderRight: "2px solid #e0e0e0", py: 1.2 }}>
                  <Typography variant="body2" fontWeight={600}>
                    Maintenance
                  </Typography>
                </TableCell>
                <TableCell align="center" sx={{ borderRight: "2px solid " + ACCENT, backgroundColor: "#fff8e1", py: 1.2 }}>
                  {currentCat?.maintenanceDiscount ? (
                    <Chip
                      label={`-${currentCat.maintenanceDiscount}%`}
                      size="small"
                      color="warning"
                      variant="outlined"
                      sx={{ fontWeight: 600 }}
                    />
                  ) : (
                    <Typography color="text.disabled">—</Typography>
                  )}
                </TableCell>
                {otherCats.map((cat: any) => (
                  <TableCell key={cat.id} align="center" sx={{ borderRight: "1px solid #e0e0e0", py: 1.2, opacity: 0.55 }}>
                    {cat.maintenanceDiscount ? (
                      <Typography variant="body2">-{cat.maintenanceDiscount}%</Typography>
                    ) : (
                      <Typography color="text.disabled">—</Typography>
                    )}
                  </TableCell>
                ))}
              </TableRow>
            )}
          </TableBody>
        </Table>
      </TableContainer>
    </Paper>
  );
};

export const PricingCategoriesEdit = () => (
  <Edit>
    <SimpleForm>
      <TextInput source="name" label="Nom" validate={required()} />
      <TextInput source="slug" label="Slug" validate={required()} />
      <TextInput source="description" label="Description" multiline />
      <NumberInput source="discountPercent" label="Remise (%)" />
      <NumberInput source="maintenanceDiscount" label="Remise maintenance (%)" />
      <BooleanInput source="isDefault" label="Grille par défaut" />
      <BooleanInput source="isActive" label="Actif" />
      <DateTimeInput source="validFrom" label="Valide du" />
      <DateTimeInput source="validUntil" label="Valide jusqu'au" />

      <Divider sx={{ mt: 3, mb: 2, width: "100%" }} />
      <Typography variant="h6" gutterBottom>
        Paliers tarifaires
      </Typography>

      <PricingTiersTable />

      <Box sx={{ mt: 1 }}>
        <AddTierButton />
      </Box>
    </SimpleForm>
  </Edit>
);
