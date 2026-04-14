import { useState, useEffect, useCallback } from 'react';
import {
  Box, Typography, Paper, Table, TableHead, TableRow, TableCell,
  TableBody, Switch, CircularProgress, Alert,
} from '@mui/material';
import PermPhoneMsgIcon from '@mui/icons-material/PermPhoneMsg';
import { useNotify, Title, useDataProvider } from 'react-admin';
import { useClient } from '../ClientProvider';
import { useSessionContext } from '../SessionContextProvider';

interface ContactItem {
  '@id': string;
  id: number;
  name: string;
}

export const ClientChannels = () => {
  const { client } = useClient();
  const { session } = useSessionContext();
  const dataProvider = useDataProvider();
  const notify = useNotify();

  const [allContacts, setAllContacts] = useState<ContactItem[]>([]);
  const [enabledIris, setEnabledIris] = useState<Set<string>>(new Set());
  const [loading, setLoading] = useState(true);
  const [pendingIds, setPendingIds] = useState<Set<number>>(new Set());

  const fetchData = useCallback(async () => {
    setLoading(true);
    try {
      const { data: all } = await dataProvider.getList('contacts', {
        sort: { field: 'name', order: 'ASC' },
        pagination: { page: 1, perPage: 100 },
        filter: {},
      });
      setAllContacts(all);

      const { data: enabled } = await dataProvider.getList('contacts', {
        sort: { field: 'name', order: 'ASC' },
        pagination: { page: 1, perPage: 100 },
        filter: { client: client.id },
      });
      setEnabledIris(new Set(enabled.map((c: any) => c['@id'])));
    } catch {
      notify('Erreur lors du chargement des canaux', { type: 'error' });
    } finally {
      setLoading(false);
    }
  }, [client?.id, dataProvider, notify]);

  useEffect(() => {
    if (client?.id) fetchData();
  }, [client?.id, fetchData]);

  const handleToggle = async (contact: ContactItem) => {
    if (pendingIds.has(contact.id)) return;

    const previous = new Set(enabledIris);
    const updated = new Set(enabledIris);
    const iri = contact['@id'];

    if (updated.has(iri)) {
      updated.delete(iri);
    } else {
      updated.add(iri);
    }

    setEnabledIris(updated);
    setPendingIds(prev => new Set(prev).add(contact.id));

    try {
      const res = await fetch(`/clients/${client.id}`, {
        method: 'PATCH',
        headers: {
          'Content-Type': 'application/merge-patch+json',
          'Accept': 'application/ld+json',
          'Authorization': `Bearer ${session?.accessToken}`,
          'X-Client-Id': String(client.id),
        },
        body: JSON.stringify({ contacts: Array.from(updated) }),
      });

      if (!res.ok) throw new Error(`HTTP ${res.status}`);
    } catch {
      setEnabledIris(previous);
      notify('Erreur lors de la mise à jour', { type: 'error' });
    } finally {
      setPendingIds(prev => { const next = new Set(prev); next.delete(contact.id); return next; });
    }
  };

  if (loading) {
    return (
      <Box sx={{ display: 'flex', justifyContent: 'center', mt: 4 }}>
        <CircularProgress />
      </Box>
    );
  }

  return (
    <Box sx={{ p: 2 }}>
      <Title title="Canaux de contact" />
      <Typography variant="h6" sx={{ mb: 2, display: 'flex', alignItems: 'center', gap: 1 }}>
        <PermPhoneMsgIcon /> Canaux de contact
      </Typography>
      <Typography variant="body2" color="text.secondary" sx={{ mb: 2 }}>
        Activez les canaux que vos clients peuvent utiliser pour vous contacter.
        Seuls les canaux actifs apparaîtront dans les formulaires de réservation.
      </Typography>
      {allContacts.length === 0 ? (
        <Alert severity="info">Aucun canal défini. Contactez un super-administrateur.</Alert>
      ) : (
        <Paper variant="outlined">
          <Table>
            <TableHead>
              <TableRow>
                <TableCell><strong>Canal</strong></TableCell>
                <TableCell align="right"><strong>Actif</strong></TableCell>
              </TableRow>
            </TableHead>
            <TableBody>
              {allContacts.map((contact) => (
                <TableRow key={contact.id} hover>
                  <TableCell>{contact.name}</TableCell>
                  <TableCell align="right">
                    <Switch
                      checked={enabledIris.has(contact['@id'])}
                      onChange={() => handleToggle(contact)}
                      disabled={pendingIds.has(contact.id)}
                      color="primary"
                    />
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </Paper>
      )}
    </Box>
  );
};
