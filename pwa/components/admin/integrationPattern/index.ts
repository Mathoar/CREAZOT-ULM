import { IntegrationPatternList } from "./IntegrationPatternList";
import { IntegrationPatternEdit } from "./IntegrationPatternEdit";
import { IntegrationPatternCreate } from "./IntegrationPatternCreate";

const integrationPatternResourceProps = {
  list: IntegrationPatternList,
  edit: IntegrationPatternEdit,
  create: IntegrationPatternCreate,
  options: { label: "Intégrations API" },
};

export default integrationPatternResourceProps;
