import { Show, SimpleShowLayout, TextField, NumberField, BooleanField } from 'react-admin';

export const OptionShow = () => (
    <Show>
        <SimpleShowLayout>
            <TextField source="nom" label="Nom de l'option"/>
            <NumberField source="prix" label="Prix" options={{ style: 'currency', currency: 'EUR' }}/>
            <BooleanField source="isAvailable" label="Disponible"/>
        </SimpleShowLayout>
    </Show>
)
