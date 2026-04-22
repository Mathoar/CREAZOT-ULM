import { Box, Card, CardContent, Typography, Skeleton } from '@mui/material';

interface KpiCardProps {
  title: string;
  value: string | number;
  subtitle?: string;
  icon?: React.ReactNode;
  color?: string;
  loading?: boolean;
}

export const KpiCard = ({ title, value, subtitle, icon, color = '#1565c0', loading }: KpiCardProps) => (
  <Card sx={{ height: '100%', borderTop: `3px solid ${color}` }}>
    <CardContent sx={{ display: 'flex', alignItems: 'center', gap: 2, py: '16px !important' }}>
      {icon && (
        <Box sx={{ width: 48, height: 48, borderRadius: '12px', backgroundColor: `${color}18`,
          display: 'flex', alignItems: 'center', justifyContent: 'center', color, flexShrink: 0 }}>
          {icon}
        </Box>
      )}
      <Box>
        <Typography variant="body2" color="text.secondary" noWrap>{title}</Typography>
        {loading ? <Skeleton width={80} height={32} /> : (
          <Typography variant="h5" fontWeight={700}>{value}</Typography>
        )}
        {subtitle && <Typography variant="caption" color="text.secondary">{subtitle}</Typography>}
      </Box>
    </CardContent>
  </Card>
);
