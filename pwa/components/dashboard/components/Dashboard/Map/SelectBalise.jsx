import { useState, useEffect } from "react";
import { isDefined } from '../../../../../app/lib/utils';
import { useDataProvider } from 'react-admin';
import { FormControl, InputLabel, MenuItem, Select, ListSubheader, Divider } from '@mui/material';
import { clientWithMicrotrakTags } from '../../../../../app/lib/client';

export const SelectBalise = ({ value, onChange, setAeronefs, client }) => {

    const dataProvider = useDataProvider();
    const hasTracking = clientWithMicrotrakTags(client);

    const [baliseChoices, setBaliseChoices] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        if (!hasTracking) {
            setLoading(false);
            return;
        }
        const fetchBalises = async () => {
            try {
                const { data } = await dataProvider.getList('aeronefs', {
                    pagination: { page: 1, perPage: 100 },
                    sort: { field: 'immatriculation', order: 'ASC' },
                });
                const balises = data.map(item => ({
                    ...item,
                    id: item.codeBalise,
                    name: item.immatriculation
                }));
                setAeronefs(data);
                setBaliseChoices(balises);
            } catch (err) {
                setError(err.message || 'Erreur de chargement');
            } finally {
                setLoading(false);
            }
        };
        fetchBalises();
    }, [hasTracking, client?.id]);

    if (isDefined(error)) return <div>Erreur : {error}</div>;
    if (loading) return <div>Chargement...</div>;

    return (
        <FormControl
            fullWidth
            size="small"
            sx={{
                backgroundColor: 'white',
                borderRadius: 1,
                minWidth: 160,
                padding: 0,
                margin: 0,
                boxShadow: 'none',
                outline: 'none',
                border: '1px solid #ccc',
                '& .MuiOutlinedInput-notchedOutline': { border: 'none' },
                '& .MuiOutlinedInput-root': { backgroundColor: 'white', padding: 0, margin: 0 },
                '& .MuiInputLabel-root': { margin: 0, padding: 0 },
            }}
        >
            <InputLabel id="balise-label" shrink sx={{ marginBottom: '6px', paddingBottom: '12px' }}>
                Vue carte
            </InputLabel>
            <Select
                labelId="balise-label"
                value={value}
                label="Vue carte"
                onChange={(e) => onChange(e.target.value)}
                sx={{ fontSize: '0.85rem', height: '42px', paddingBottom: '0', paddingTop: '8px' }}
            >
                <MenuItem value="none">Aucune</MenuItem>
                <MenuItem value="traffic">
                    <span style={{ display: 'flex', alignItems: 'center', gap: 6 }}>
                        ✈ Trafic aérien
                    </span>
                </MenuItem>

                {hasTracking && baliseChoices.length > 0 && <Divider />}

                {hasTracking && baliseChoices.length > 0 && (
                    <ListSubheader sx={{ fontSize: '0.75rem', lineHeight: '28px' }}>
                        Mes balises
                    </ListSubheader>
                )}

                {hasTracking && (
                    <MenuItem value="all">Toutes les balises</MenuItem>
                )}

                {hasTracking && baliseChoices.map((choice, i) => (
                    <MenuItem key={i} value={choice.id}>
                        {choice.name}
                    </MenuItem>
                ))}

                {hasTracking && baliseChoices.length > 0 && <Divider />}

                {hasTracking && (
                    <MenuItem value="all_traffic">
                        <span style={{ display: 'flex', alignItems: 'center', gap: 6 }}>
                            🔀 Balises + Trafic
                        </span>
                    </MenuItem>
                )}
            </Select>
        </FormControl>
    );
};
