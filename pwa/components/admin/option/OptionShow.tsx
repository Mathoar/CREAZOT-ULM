import { Show, SimpleShowLayout, TextField, NumberField, BooleanField, FunctionField } from 'react-admin';

export const OptionShow = () => (
    <Show>
        <SimpleShowLayout>
            <TextField source="nom" label="Nom de l'option"/>
            <NumberField source="prix" label="Prix TTC" options={{ style: 'currency', currency: 'EUR' }}/>
            <FunctionField
                source="tauxTva"
                label="TVA"
                render={record => record.tauxTva != null ? `${(record.tauxTva * 100).toFixed(1)} %` : '—'}
            />
            <NumberField source="prixHT" label="Prix HT" options={{ style: 'currency', currency: 'EUR' }}/>
            <BooleanField source="isAvailable" label="Disponible"/>
        </SimpleShowLayout>
    </Show>
)
