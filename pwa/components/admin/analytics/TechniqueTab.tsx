import { useMemo } from 'react';
import { Box, Grid, Typography, Skeleton, Card, CardContent, Chip, LinearProgress, Table, TableBody, TableCell, TableContainer, TableHead, TableRow, Paper } from '@mui/material';
import dynamic from 'next/dynamic';
import { KpiCard } from './KpiCard';
import BuildIcon from '@mui/icons-material/Build';
import SpeedIcon from '@mui/icons-material/Speed';
import WarningAmberIcon from '@mui/icons-material/WarningAmber';
import CheckCircleIcon from '@mui/icons-material/CheckCircle';

const Chart = dynamic(() => import('react-apexcharts'), { ssr: false });

const chartDefaults = {
  chart: { toolbar: { show: false }, fontFamily: 'inherit' },
  dataLabels: { enabled: false },
  stroke: { curve: 'smooth' as const, width: 2 },
  grid: { borderColor: '#f0f0f0' },
};

const fmtH = (n: number) => `${Math.round(n * 10) / 10}h`;

const maintenanceColor = (remaining: number, seuil: number) => {
  if (remaining <= 0) return 'error';
  if (remaining <= seuil) return 'warning';
  return 'success';
};

const progressPercent = (horametre: number, entretien: number) => {
  if (entretien <= 0) return 100;
  return Math.min(100, Math.round((horametre / entretien) * 100));
};

interface Props { data: any; loading: boolean; }

export const TechniqueTab = ({ data, loading }: Props) => {
  if (loading || !data) {
    return (
      <Grid container spacing={2}>
        {Array.from({ length: 6 }).map((_, i) => (
          <Grid item xs={12} md={6} lg={3} key={i}><Skeleton variant="rounded" height={140} /></Grid>
        ))}
      </Grid>
    );
  }

  const { fleet_status, maintenance_history, horametre_evolution, maintenance_forecast } = data;

  const nbAeronefs = fleet_status?.length ?? 0;
  const nbAlerts = fleet_status?.filter((a: any) => Number(a.heures_avant_maintenance) <= Number(a.seuil_alerte)).length ?? 0;
  const nbMoteurAlerts = fleet_status?.filter((a: any) => Number(a.heures_avant_moteur) <= Number(a.seuil_alerte_changement_moteur)).length ?? 0;
  const nbMaintenances = maintenance_history?.length ?? 0;

  const horametreData = useMemo(() => {
    if (!horametre_evolution?.length) return { series: [], categories: [] };
    const aircrafts = Array.from(new Set(horametre_evolution.map((h: any) => h.immatriculation))) as string[];
    const periods = Array.from(new Set(horametre_evolution.map((h: any) => h.period))) as string[];
    const series = aircrafts.map(ac => ({
      name: ac,
      data: periods.map(p => {
        const match = horametre_evolution.find((h: any) => h.immatriculation === ac && h.period === p);
        return match ? Math.round(Number(match.heures) * 10) / 10 : 0;
      }),
    }));
    return { series, categories: periods };
  }, [horametre_evolution]);

  return (
    <Box sx={{ display: 'flex', flexDirection: 'column', gap: 3 }}>

      <Grid container spacing={2}>
        <Grid item xs={6} md={3}>
          <KpiCard title="Flotte" value={nbAeronefs} icon={<SpeedIcon />} color="#1565c0" />
        </Grid>
        <Grid item xs={6} md={3}>
          <KpiCard title="Alertes entretien" value={nbAlerts}
            icon={nbAlerts > 0 ? <WarningAmberIcon /> : <CheckCircleIcon />}
            color={nbAlerts > 0 ? '#ed6c02' : '#2e7d32'} />
        </Grid>
        <Grid item xs={6} md={3}>
          <KpiCard title="Alertes moteur" value={nbMoteurAlerts}
            icon={nbMoteurAlerts > 0 ? <WarningAmberIcon /> : <CheckCircleIcon />}
            color={nbMoteurAlerts > 0 ? '#d32f2f' : '#2e7d32'} />
        </Grid>
        <Grid item xs={6} md={3}>
          <KpiCard title="Interventions" value={nbMaintenances} subtitle="sur la période"
            icon={<BuildIcon />} color="#9c27b0" />
        </Grid>
      </Grid>

      {/* Fleet status table */}
      <Card>
        <CardContent>
          <Typography variant="subtitle1" fontWeight={600} gutterBottom>État de la flotte</Typography>
          <TableContainer>
            <Table size="small">
              <TableHead>
                <TableRow>
                  <TableCell sx={{ fontWeight: 600 }}>Aéronef</TableCell>
                  <TableCell sx={{ fontWeight: 600 }} align="right">Horamètre</TableCell>
                  <TableCell sx={{ fontWeight: 600 }} align="right">Prochain entretien</TableCell>
                  <TableCell sx={{ fontWeight: 600 }}>Avant entretien</TableCell>
                  <TableCell sx={{ fontWeight: 600 }} align="right">Changement moteur</TableCell>
                  <TableCell sx={{ fontWeight: 600 }}>Avant moteur</TableCell>
                  <TableCell sx={{ fontWeight: 600 }} align="center">Dispo</TableCell>
                </TableRow>
              </TableHead>
              <TableBody>
                {fleet_status?.map((a: any) => {
                  const remaining = Number(a.heures_avant_maintenance);
                  const seuil = Number(a.seuil_alerte);
                  const remainingMoteur = Number(a.heures_avant_moteur);
                  const seuilMoteur = Number(a.seuil_alerte_changement_moteur);
                  return (
                    <TableRow key={a.immatriculation}>
                      <TableCell sx={{ fontWeight: 600 }}>{a.immatriculation}</TableCell>
                      <TableCell align="right">{fmtH(Number(a.horametre))}</TableCell>
                      <TableCell align="right">{fmtH(Number(a.entretien))}</TableCell>
                      <TableCell>
                        <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                          <LinearProgress variant="determinate"
                            value={progressPercent(Number(a.horametre), Number(a.entretien))}
                            color={maintenanceColor(remaining, seuil)}
                            sx={{ flexGrow: 1, height: 8, borderRadius: 4 }} />
                          <Chip label={fmtH(remaining)} size="small"
                            color={maintenanceColor(remaining, seuil)} variant="outlined" />
                        </Box>
                      </TableCell>
                      <TableCell align="right">{fmtH(Number(a.changement_moteur))}</TableCell>
                      <TableCell>
                        <Chip label={fmtH(remainingMoteur)} size="small"
                          color={maintenanceColor(remainingMoteur, seuilMoteur)} variant="outlined" />
                      </TableCell>
                      <TableCell align="center">
                        <Chip label={a.is_available ? 'Oui' : 'Non'} size="small"
                          color={a.is_available ? 'success' : 'default'} />
                      </TableCell>
                    </TableRow>
                  );
                })}
              </TableBody>
            </Table>
          </TableContainer>
        </CardContent>
      </Card>

      {/* Horametre evolution + Forecast */}
      <Grid container spacing={2}>
        <Grid item xs={12} md={7}>
          <Card sx={{ height: '100%' }}>
            <CardContent>
              <Typography variant="subtitle1" fontWeight={600} gutterBottom>Évolution heures de vol par aéronef</Typography>
              {horametreData.series.length > 0 ? (
                <Chart type="bar" height={300} options={{
                  ...chartDefaults,
                  chart: { ...chartDefaults.chart, stacked: true },
                  xaxis: { categories: horametreData.categories },
                  colors: ['#1565c0', '#2e7d32', '#ed6c02', '#9c27b0', '#d32f2f', '#795548'],
                  legend: { position: 'bottom' },
                  plotOptions: { bar: { borderRadius: 2, columnWidth: '60%' } },
                  tooltip: { y: { formatter: (v: number) => fmtH(v) } },
                }} series={horametreData.series} />
              ) : <Typography color="text.secondary">Aucune donnée</Typography>}
            </CardContent>
          </Card>
        </Grid>
        <Grid item xs={12} md={5}>
          <Card sx={{ height: '100%' }}>
            <CardContent>
              <Typography variant="subtitle1" fontWeight={600} gutterBottom>Estimation maintenance</Typography>
              <Typography variant="caption" color="text.secondary" gutterBottom sx={{ display: 'block', mb: 2 }}>
                Jours de vol estimés avant maintenance (basé sur la consommation moyenne)
              </Typography>
              {maintenance_forecast?.map((a: any) => (
                <Box key={a.immatriculation} sx={{ mb: 2, display: 'flex', alignItems: 'center', gap: 2 }}>
                  <Typography variant="body2" fontWeight={600} sx={{ minWidth: 80 }}>{a.immatriculation}</Typography>
                  <Box sx={{ flexGrow: 1 }}>
                    <LinearProgress variant="determinate"
                      value={progressPercent(Number(a.horametre), Number(a.entretien))}
                      color={Number(a.jours_estimes) !== null && Number(a.jours_estimes) < 10 ? 'warning' : 'primary'}
                      sx={{ height: 10, borderRadius: 5 }} />
                  </Box>
                  <Chip
                    label={a.jours_estimes !== null ? `~${a.jours_estimes} jours` : 'N/A'}
                    size="small" variant="outlined"
                    color={a.jours_estimes !== null && Number(a.jours_estimes) < 10 ? 'warning' : 'default'} />
                </Box>
              ))}
            </CardContent>
          </Card>
        </Grid>
      </Grid>

      {/* Maintenance history */}
      {maintenance_history?.length > 0 && (
        <Card>
          <CardContent>
            <Typography variant="subtitle1" fontWeight={600} gutterBottom>Historique des interventions</Typography>
            <TableContainer>
              <Table size="small">
                <TableHead>
                  <TableRow>
                    <TableCell sx={{ fontWeight: 600 }}>Date</TableCell>
                    <TableCell sx={{ fontWeight: 600 }}>Aéronef</TableCell>
                    <TableCell sx={{ fontWeight: 600 }}>Intervention</TableCell>
                    <TableCell sx={{ fontWeight: 600 }} align="right">Horamètre</TableCell>
                    <TableCell sx={{ fontWeight: 600 }} align="right">Prochain</TableCell>
                    <TableCell sx={{ fontWeight: 600 }} align="center">Moteur</TableCell>
                  </TableRow>
                </TableHead>
                <TableBody>
                  {maintenance_history.map((m: any, i: number) => (
                    <TableRow key={i}>
                      <TableCell>{new Date(m.date).toLocaleDateString('fr-FR')}</TableCell>
                      <TableCell sx={{ fontWeight: 600 }}>{m.immatriculation}</TableCell>
                      <TableCell>{m.intervention}</TableCell>
                      <TableCell align="right">{fmtH(Number(m.horametre_intervention))}</TableCell>
                      <TableCell align="right">{fmtH(Number(m.horametre_next_intervention))}</TableCell>
                      <TableCell align="center">
                        {m.changement_moteur ? <Chip label="Oui" size="small" color="warning" /> : '—'}
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </TableContainer>
          </CardContent>
        </Card>
      )}
    </Box>
  );
};
