import { Button } from '@mui/material';
import BackupTableIcon from '@mui/icons-material/BackupTable';
import PictureAsPdfIcon from '@mui/icons-material/PictureAsPdf';
import { useListContext } from 'react-admin';
import { useSessionContext } from '../SessionContextProvider';

const buildExportParams = (filterValues: Record<string, any>): URLSearchParams => {
  const params = new URLSearchParams();
  Object.entries(filterValues).forEach(([key, value]) => {
    if (value && typeof value === 'object' && value.after) {
      if (value.after) params.append(`${key}[after]`, value.after);
      if (value.before) params.append(`${key}[before]`, value.before);
    } else if (value != null) {
      params.append(key, String(value));
    }
  });
  return params;
};

interface ExportButtonProps {
  isSmall: boolean;
  resource: string;
}

export const ExportCSVButton = ({ isSmall, resource }: ExportButtonProps) => {
  const { filterValues } = useListContext();
  const { session } = useSessionContext();

  const handleExport = async () => {
    const params = buildExportParams(filterValues);
    const url = `/exports/${resource}?${params.toString()}&format=csv`;
    const response = await fetch(url, {
      headers: { Authorization: `Bearer ${session?.accessToken}` },
    });
    const blob = await response.blob();
    const blobUrl = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = blobUrl;
    a.download = `${resource}.csv`;
    a.click();
    window.URL.revokeObjectURL(blobUrl);
  };

  return (
    <Button size="small" color="primary" onClick={handleExport} startIcon={<BackupTableIcon />}>
      {!isSmall && 'EXPORT CSV'}
    </Button>
  );
};

export const ExportPDFButton = ({ isSmall, resource }: ExportButtonProps) => {
  const { filterValues } = useListContext();
  const { session } = useSessionContext();

  const handleExport = async () => {
    const params = buildExportParams(filterValues);
    const url = `/exports/${resource}?${params.toString()}&format=pdf`;
    const response = await fetch(url, {
      headers: { Authorization: `Bearer ${session?.accessToken}` },
    });
    const blob = await response.blob();
    const blobUrl = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = blobUrl;
    a.download = `${resource}.pdf`;
    a.click();
    window.URL.revokeObjectURL(blobUrl);
  };

  return (
    <Button size="small" color="primary" onClick={handleExport} startIcon={<PictureAsPdfIcon />}>
      {!isSmall && 'EXPORT PDF'}
    </Button>
  );
};
