import { useMemo } from 'react';
import { Box, Grid, Typography, Skeleton, Card, CardContent, Table, TableBody, TableCell, TableContainer, TableHead, TableRow, Chip } from '@mui/material';
import dynamic from 'next/dynamic';
import { KpiCard } from './KpiCard';
import ReceiptLongIcon from '@mui/icons-material/ReceiptLong';
import AccountBalanceIcon from '@mui/icons-material/AccountBalance';
import TrendingDownIcon from '@mui/icons-material/TrendingDown';
import BalanceIcon from '@mui/icons-material/Balance';

const Chart = dynamic(() => import('react-apexcharts'), { ssr: false });

const fmt = (n: number) => new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR', maximumFractionDigits: 2 }).format(n);
const fmtPct = (n: number) => `${(n * 100).toFixed(1).replace('.', ',')} %`;

const chartDefaults = {
  chart: { toolbar: { show: false }, fontFamily: 'inherit' },
  dataLabels: { enabled: false },
  stroke: { curve: 'smooth' as const, width: 2 },
  grid: { borderColor: '#f0f0f0' },
};

interface Props { data: any; loading: boolean; }

export const FiscalTab = ({ data, loading }: Props) => {
  if (loading || !data) {
    return (
      <Grid container spacing={2}>
        {[...Array(6)].map((_, i) => (
          <Grid item xs={12} md={6} lg={3} key={i}><Skeleton variant="rounded" height={140} /></Grid>
        ))}
      </Grid>
    );
  }

  const { tva_collectee, tva_deductible, tva_par_taux, synthese } = data;

  const timelineData = useMemo(() => {
    const allPeriods = new Set<string>();
    tva_collectee?.forEach((r: any) => allPeriods.add(r.period));
    tva_deductible?.forEach((r: any) => allPeriods.add(r.period));
    const periods = Array.from(allPeriods).sort();

    const collecteeMap = new Map(tva_collectee?.map((r: any) => [r.period, r]) ?? []);
    const deductibleMap = new Map(tva_deductible?.map((r: any) => [r.period, r]) ?? []);

    return {
      periods,
      collectee: periods.map(p => Number((collecteeMap.get(p) as any)?.total_tva ?? 0)),
      deductible: periods.map(p => Number((deductibleMap.get(p) as any)?.total_tva ?? 0)),
      nette: periods.map(p => {
        const c = Number((collecteeMap.get(p) as any)?.total_tva ?? 0);
        const d = Number((deductibleMap.get(p) as any)?.total_tva ?? 0);
        return +(c - d).toFixed(2);
      }),
    };
  }, [tva_collectee, tva_deductible]);

  return (
    <Box sx={{ display: 'flex', flexDirection: 'column', gap: 3 }}>

      {/* KPI */}
      <Grid container spacing={2}>
        <Grid item xs={6} md={3}>
          <KpiCard title="TVA collectée" value={fmt(synthese?.tva_collectee ?? 0)} subtitle="Sur paiements" icon={<ReceiptLongIcon />} color="#2e7d32" />
        </Grid>
        <Grid item xs={6} md={3}>
          <KpiCard title="TVA déductible" value={fmt(synthese?.tva_deductible ?? 0)} subtitle="Sur dépenses" icon={<TrendingDownIcon />} color="#d32f2f" />
        </Grid>
        <Grid item xs={6} md={3}>
          <KpiCard title="TVA nette" value={fmt(synthese?.tva_nette ?? 0)}
            subtitle={synthese?.tva_nette >= 0 ? 'À reverser' : 'Crédit de TVA'}
            icon={<BalanceIcon />}
            color={synthese?.tva_nette >= 0 ? '#ed6c02' : '#0288d1'} />
        </Grid>
        <Grid item xs={6} md={3}>
          <KpiCard title="Base HT encaissée"
            value={fmt(tva_collectee?.reduce((s: number, r: any) => s + Number(r.total_ht), 0) ?? 0)}
            icon={<AccountBalanceIcon />} color="#1565c0" />
        </Grid>
      </Grid>

      {/* Timeline chart */}
      <ChartCard title="Évolution TVA collectée / déductible / nette">
        <Chart type="area" height={320} options={{
          ...chartDefaults,
          xaxis: { categories: timelineData.periods },
          yaxis: { labels: { formatter: (v: number) => fmt(v) } },
          colors: ['#2e7d32', '#d32f2f', '#ed6c02'],
          fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.05 } },
          tooltip: { y: { formatter: (v: number) => fmt(v) } },
          legend: { position: 'top' },
        }}
        series={[
          { name: 'TVA collectée', data: timelineData.collectee },
          { name: 'TVA déductible', data: timelineData.deductible },
          { name: 'TVA nette', data: timelineData.nette },
        ]}
        />
      </ChartCard>

      {/* Breakdown by rate */}
      <Grid container spacing={2}>
        <Grid item xs={12} md={6}>
          <ChartCard title="TVA collectée par taux">
            <TvaRateTable rows={tva_par_taux?.collectee ?? []} />
          </ChartCard>
        </Grid>
        <Grid item xs={12} md={6}>
          <ChartCard title="TVA déductible par taux">
            <TvaRateTable rows={tva_par_taux?.deductible ?? []} />
          </ChartCard>
        </Grid>
      </Grid>

      {/* Detailed timeline table */}
      <ChartCard title="Détail par période">
        <TableContainer>
          <Table size="small">
            <TableHead>
              <TableRow>
                <TableCell><strong>Période</strong></TableCell>
                <TableCell align="right"><strong>CA TTC</strong></TableCell>
                <TableCell align="right"><strong>CA HT</strong></TableCell>
                <TableCell align="right"><strong>TVA collectée</strong></TableCell>
                <TableCell align="right"><strong>Dépenses TTC</strong></TableCell>
                <TableCell align="right"><strong>Dépenses HT</strong></TableCell>
                <TableCell align="right"><strong>TVA déductible</strong></TableCell>
                <TableCell align="right"><strong>TVA nette</strong></TableCell>
              </TableRow>
            </TableHead>
            <TableBody>
              {timelineData.periods.map((period, i) => {
                const c = tva_collectee?.find((r: any) => r.period === period);
                const d = tva_deductible?.find((r: any) => r.period === period);
                const nette = timelineData.nette[i];
                return (
                  <TableRow key={period} hover>
                    <TableCell>{period}</TableCell>
                    <TableCell align="right">{fmt(Number(c?.total_ttc ?? 0))}</TableCell>
                    <TableCell align="right">{fmt(Number(c?.total_ht ?? 0))}</TableCell>
                    <TableCell align="right" sx={{ color: '#2e7d32', fontWeight: 600 }}>{fmt(Number(c?.total_tva ?? 0))}</TableCell>
                    <TableCell align="right">{fmt(Number(d?.total_ttc ?? 0))}</TableCell>
                    <TableCell align="right">{fmt(Number(d?.total_ht ?? 0))}</TableCell>
                    <TableCell align="right" sx={{ color: '#d32f2f', fontWeight: 600 }}>{fmt(Number(d?.total_tva ?? 0))}</TableCell>
                    <TableCell align="right" sx={{ fontWeight: 700, color: nette >= 0 ? '#ed6c02' : '#0288d1' }}>
                      {fmt(nette)}
                    </TableCell>
                  </TableRow>
                );
              })}
              {timelineData.periods.length > 0 && (
                <TableRow sx={{ backgroundColor: '#f5f5f5' }}>
                  <TableCell><strong>TOTAL</strong></TableCell>
                  <TableCell align="right"><strong>{fmt(tva_collectee?.reduce((s: number, r: any) => s + Number(r.total_ttc), 0) ?? 0)}</strong></TableCell>
                  <TableCell align="right"><strong>{fmt(tva_collectee?.reduce((s: number, r: any) => s + Number(r.total_ht), 0) ?? 0)}</strong></TableCell>
                  <TableCell align="right" sx={{ color: '#2e7d32' }}><strong>{fmt(synthese?.tva_collectee ?? 0)}</strong></TableCell>
                  <TableCell align="right"><strong>{fmt(tva_deductible?.reduce((s: number, r: any) => s + Number(r.total_ttc), 0) ?? 0)}</strong></TableCell>
                  <TableCell align="right"><strong>{fmt(tva_deductible?.reduce((s: number, r: any) => s + Number(r.total_ht), 0) ?? 0)}</strong></TableCell>
                  <TableCell align="right" sx={{ color: '#d32f2f' }}><strong>{fmt(synthese?.tva_deductible ?? 0)}</strong></TableCell>
                  <TableCell align="right" sx={{ fontWeight: 700, color: synthese?.tva_nette >= 0 ? '#ed6c02' : '#0288d1' }}>
                    <strong>{fmt(synthese?.tva_nette ?? 0)}</strong>
                  </TableCell>
                </TableRow>
              )}
            </TableBody>
          </Table>
        </TableContainer>
      </ChartCard>
    </Box>
  );
};

const ChartCard = ({ title, children }: { title: string; children: React.ReactNode }) => (
  <Card sx={{ height: '100%' }}>
    <CardContent>
      <Typography variant="subtitle1" fontWeight={600} gutterBottom>{title}</Typography>
      {children}
    </CardContent>
  </Card>
);

const TvaRateTable = ({ rows }: { rows: any[] }) => {
  if (!rows.length) return <Typography color="text.secondary" sx={{ py: 2 }}>Aucune donnée</Typography>;

  return (
    <TableContainer>
      <Table size="small">
        <TableHead>
          <TableRow>
            <TableCell><strong>Taux</strong></TableCell>
            <TableCell align="right"><strong>Base TTC</strong></TableCell>
            <TableCell align="right"><strong>Montant TVA</strong></TableCell>
          </TableRow>
        </TableHead>
        <TableBody>
          {rows.map((r: any, i: number) => (
            <TableRow key={i} hover>
              <TableCell>
                <Chip label={fmtPct(Number(r.taux))} size="small" variant="outlined"
                  color={Number(r.taux) === 0.2 ? 'primary' : Number(r.taux) === 0.085 ? 'success' : 'default'} />
              </TableCell>
              <TableCell align="right">{fmt(Number(r.total_ttc))}</TableCell>
              <TableCell align="right" sx={{ fontWeight: 600 }}>{fmt(Number(r.tva))}</TableCell>
            </TableRow>
          ))}
          {rows.length > 1 && (
            <TableRow sx={{ backgroundColor: '#f5f5f5' }}>
              <TableCell><strong>Total</strong></TableCell>
              <TableCell align="right"><strong>{fmt(rows.reduce((s, r) => s + Number(r.total_ttc), 0))}</strong></TableCell>
              <TableCell align="right"><strong>{fmt(rows.reduce((s, r) => s + Number(r.tva), 0))}</strong></TableCell>
            </TableRow>
          )}
        </TableBody>
      </Table>
    </TableContainer>
  );
};
