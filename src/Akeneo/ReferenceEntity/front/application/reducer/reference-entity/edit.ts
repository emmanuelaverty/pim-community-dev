import user, {UserState} from 'akeneoreferenceentity/application/reducer/user';
import sidebar, {SidebarState} from 'akeneoreferenceentity/application/reducer/sidebar';
import grid, {GridState} from 'akeneoreferenceentity/application/reducer/grid';
import createRecord, {CreateState as CreateRecordState} from 'akeneoreferenceentity/application/reducer/record/create';
import form, {EditionFormState} from 'akeneoreferenceentity/application/reducer/reference-entity/edit/form';
import Record from 'akeneoreferenceentity/domain/model/record/record';
import createAttribute, {
  CreateState as CreateAttributeState,
} from 'akeneoreferenceentity/application/reducer/attribute/create';
import structure, {StructureState} from 'akeneoreferenceentity/application/reducer/structure';
import attributes, {ListState} from 'akeneoreferenceentity/application/reducer/attribute/list';
import attribute, {EditState as EditAttributeState} from 'akeneoreferenceentity/application/reducer/attribute/edit';

export interface EditState {
  user: UserState;
  sidebar: SidebarState;
  grid: GridState<Record>;
  createRecord: CreateRecordState;
  createAttribute: CreateAttributeState;
  attributes: ListState;
  attribute: EditAttributeState;
  form: EditionFormState;
  structure: StructureState;
}

export default {
  user,
  sidebar,
  grid,
  createRecord,
  createAttribute,
  attributes,
  attribute,
  structure,
  form,
};
