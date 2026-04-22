import { Show, TabbedShowLayout, TextField, DateField, NumberField, BooleanField, FunctionField, RichTextField } from 'react-admin';
import Chip from '@mui/material/Chip';
import { Box, Typography } from '@mui/material';
import { useClient } from '../../admin/ClientProvider';
import { clientWithOptions, clientWithWebshop, clientWithLandingManagement, clientWithPlanification } from '../../../app/lib/client';

export const CircuitShow = () => {

    const { client } = useClient();

    const getShipStyle = ({ color }) => ({
        backgroundColor: color + '33',
        color: color,
        border: '1px solid',
        borderColor: color,
        marginRight: '4px',
        marginBottom: '2px',
        marginTop: '2px'
    });

    return (
        <Show>
            <TabbedShowLayout>
                <TabbedShowLayout.Tab label="Paramètres">
                    <TextField source="nom" />
                    <TextField source="code" label="Code interne"/>
                    { clientWithWebshop(client) && <TextField source="webshopId" label="Code e-commerce"/> }
                    <DateField source="duree" label="Durée" showTime showDate={false}/>
                    <BooleanField source="prixFixe" label="Prix fixe"/>
                    <NumberField source="prix" options={{ style: 'currency', currency: 'EUR' }}/>
                    <NumberField source="cout" label="Coût pilote" options={{ style: 'currency', currency: 'EUR' }}/>
                    <TextField source="nature.label" label="Nature de la prestation"/>
                    <FunctionField
                        label="Qualifications"
                        render={record => record.qualifications?.map((q, i) => <Chip key={i} label={q.slug} size="small" sx={ getShipStyle(q) }/>)}
                    />
                    { clientWithOptions(client) && <BooleanField source="avecOptions" label="Options disponibles"/> }
                    <BooleanField source="needsEncadrant" label="Encadrant requis"/>
                    { clientWithLandingManagement(client) && <BooleanField source="requireLandingDeclaration" label="Déclaration atterrissages"/> }
                    { clientWithLandingManagement(client) && <BooleanField source="hadDefaultLanding" label="Atterrissage par défaut"/> }
                </TabbedShowLayout.Tab>
                { clientWithPlanification(client) &&
                    <TabbedShowLayout.Tab label="Briefing commercial">
                        <FunctionField
                            label="Image d'illustration"
                            render={record => {
                                const img = record.briefingImage;
                                if (!img) return <Typography variant="body2" color="text.secondary">Aucune image</Typography>;
                                const url = img.contentUrl || img;
                                return typeof url === 'string' ? (
                                    <Box>
                                        <img src={url} alt="Briefing" style={{ maxWidth: '100%', maxHeight: 300, borderRadius: 8 }} />
                                    </Box>
                                ) : null;
                            }}
                        />
                        <RichTextField source="briefingHtml" label="Texte du briefing circuit"/>
                    </TabbedShowLayout.Tab>
                }
            </TabbedShowLayout>
        </Show>
    )
}
