import { type NextPage } from "next";
import {
  Datagrid,
  List,
  TextField,
  BooleanField,
  CreateButton,
  ExportButton,
  TopToolbar,
  EditButton,
  SimpleList,
  ShowButton
} from "react-admin";
import { useMercure } from "../../../utils/mercure";
import { type Contact } from "../../../types/Contact";
import { useMediaQuery, Theme, Chip } from '@mui/material';
import { type PagedCollection } from "../../../types/collection";
import { FunctionField } from "react-admin";


export interface Props {
  data: PagedCollection<Contact> | null;
  hubURL: string | null;
  page: number;
}

const ListActions = () => (
  <TopToolbar>
      <CreateButton/>
      <ExportButton/>
  </TopToolbar>
);

export const NaturesList: NextPage<Props> = ({ data, hubURL, page }) => {
  const collection = useMercure(data, hubURL);
  const isSmall = useMediaQuery<Theme>(theme => theme.breakpoints.down('sm'));

  return (
    <List resource="natures" actions={<ListActions/>}>
        { isSmall ? 
            <SimpleList
              primaryText={ record => record.code }
              secondaryText={ record => `${record.label}${record.isParticularActivity ? ' (AP)' : ''}`}
              linkType="edit"
            /> 
            :
            <Datagrid sx={{ '& .RaDatagrid-headerCell': {backgroundColor: '#ededed', fontWeight: "lighter"}}}>
                <TextField source="code" label="Code" sortable={ true }/>
                <TextField source="label" label="Label"/>
                <FunctionField label="Type" render={(record: any) => 
                  record.isParticularActivity 
                    ? <Chip label="Activité Particulière" size="small" color="warning" variant="outlined" />
                    : <Chip label="Standard" size="small" variant="outlined" />
                } />
                <p className="text-right">
                    <ShowButton />
                    <EditButton />
                </p>
            </Datagrid>
        }
    </List>
  );
}