/**
 * Every block starts by registering a new block type definition.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */

import { registerBlockType } from '@wordpress/blocks';
import { useSelect } from '@wordpress/data';
import { SelectControl, RangeControl, CheckboxControl, Placeholder, PanelBody } from '@wordpress/components';
import { InspectorControls, PanelColorSettings } from '@wordpress/block-editor';
import IgnitionDeckIcon from '../icon.js';

registerBlockType("idcf-blocks/grid-project", {
    title: "Project Grid",
    category: "ignitiondeck",
    description: "A block that displays multiple crowdfunding projects in a nice, easy-to-configure, grid layout.",
    icon: IgnitionDeckIcon,
    supports: {
		"html": false
	},
    textdomain: "idcf_blocks",
    attributes: {
        projectsType: {
            type: "string",
            default: "all"
        },
        projectsCategory: {
            type: "integer",
            default: 0
        },
        columnsInGrid: {
            type: "integer",
            default: 3
        },
        projectsInGrid: {
            type: "integer",
            default: 6
        },
        allProjects: {
            type: "boolean",
            default: false
        },
        showExcerpt: {
            type: "boolean",
            default: true
        },
        showImage: {
            type: "boolean",
            default: true
        },
        showBadge: {
            type: "boolean",
            default: false
        },
        projectsTitleSize: {
            type: "string",
            default: "h2"
        },
        titleColor: {
			type: "string",
			default: "#000"
		},
		descriptionColor: {
			type: "string",
			default: "#000"
		},
		barChartColor: {
			type: "string",
			default: "#3182CE"
		},
        categoryTagColor: {
			type: "string",
			default: "#3182CE"
		},
		metaColor: {
			type: "string",
			default: "#000"
		},
        badgeColor: {
			type: "string",
			default: "#3182CE"
		},
    },
    edit:({attributes, setAttributes}) => {
        const colors = useSelect('core/block-editor').getSettings().colors;

        const projects = useSelect( ( select ) => {
            const query = {
                _embed: true
            };

            if(attributes.allProjects === false) {
                query.per_page = attributes.projectsInGrid;
            }

            if(attributes.projectsCategory !== 0) {
                query.project_category = attributes.projectsCategory;
            }
            return select( 'core' ).getEntityRecords( 'postType', 'ignition_product', query );
        } );

        const projectsCategories = useSelect( ( select ) => {
            return select( 'core' ).getEntityRecords( 'taxonomy', 'project_category' );
        } );

        return [
            <InspectorControls>
                <PanelBody>
                    <SelectControl 
                        label="Choose projects by status"
                        value={ attributes.projectsType }
                        options={[
                            {label: 'All', value: 'all'},
                            {label: 'Active', value: 'active'},
                            {label: 'Successful', value: 'successful'},
                            {label: 'Failed', value: 'failed'},
                        ]}
                        onChange={ ( value ) => setAttributes( {projectsType: value} ) }
                    />

                    <SelectControl 
                        label="Filter by category"
                        value={ attributes.projectsCategory }
                        options={ 
                            projectsCategories instanceof Array
                            ? [].concat([{label: 'All', value: 0}], projectsCategories.map(({name, id}) => ({label: name, value: id})))
                            : [{label: '', value: -1}]
                        }
                        onChange={ ( value ) => setAttributes( {projectsCategory: parseInt(value)} ) }
                    />

                    <RangeControl
                        label="Columns in grid"
                        value={ attributes.columnsInGrid }
                        onChange={ ( value ) => setAttributes( {columnsInGrid: value} ) }
                        min={ 1 }
                        max={ 6 }
                    />

                    <CheckboxControl
                        label="Show all projects"
                        help="Check this option to show all projects"
                        checked={ attributes.allProjects }
                        onChange={ ( value ) => setAttributes( {allProjects: value} ) }
                    />

                    <RangeControl
                        label="Projects in grid"
                        value={ attributes.projectsInGrid }
                        disabled={ attributes.allProjects ? true : false }
                        onChange={ ( value ) => setAttributes( {projectsInGrid: value} ) }
                        min={ 1 }
                        max={ 50 }
                    />

                    <SelectControl 
                        label="Select title heading level"
                        value={ attributes.projectsTitleSize }
                        options={[
                            {label: "H2", value: "h2"},
                            {label: "H3", value: "h3"},
                            {label: "H4", value: "h4"},
                            {label: "H5", value: "h5"}
                        ]}
                        onChange={ ( value ) => setAttributes( {projectsTitleSize: value} ) }
                    />

                    <CheckboxControl
                        label="Show Excerpt"
                        help="Uncheck this option to hide the excerpt"
                        checked={ attributes.showExcerpt }
                        onChange={ ( value ) => setAttributes( {showExcerpt: value} ) }
                    />

                    <CheckboxControl
                        label="Show Image"
                        help="Uncheck this option to hide the image"
                        checked={ attributes.showImage }
                        onChange={ ( value ) => setAttributes( {showImage: value} ) }
                    />

                    <CheckboxControl
                        label="Show Badge"
                        help="Check this option to show the Successful/Failed badge"
                        checked={ attributes.showBadge }
                        onChange={ ( value ) => setAttributes( {showBadge: value} ) }
                    />

                    <PanelColorSettings
                        title='Color Settings'
                        colors={ colors }
                        initialOpen={false}
                        colorSettings={ [
                            {
                                value: attributes.titleColor,
                                onChange:( colorValue ) => setAttributes( { titleColor: colorValue } ),
                                label: 'Title Color',
                            },
                            {
                                value: attributes.descriptionColor,
                                onChange: ( colorValue ) => setAttributes( { descriptionColor: colorValue } ),
                                label: 'Description Color',
                            },
                            {
                                value: attributes.barChartColor,
                                onChange:( colorValue ) => setAttributes( { barChartColor: colorValue } ),
                                label: 'Bar Chart',
                            },
                            {
                                value: attributes.categoryTagColor,
                                onChange:( colorValue ) => setAttributes( { categoryTagColor: colorValue } ),
                                label: 'Category Tag Color',
                            },
                            {
                                value: attributes.badgeColor,
                                onChange:( colorValue ) => setAttributes( { badgeColor: colorValue } ),
                                label: 'Badge Color',
                            },
                            {
                                value: attributes.metaColor,
                                onChange: ( colorValue ) => setAttributes( { metaColor: colorValue } ),
                                label: 'Meta Color',
                            },
                        ] }
                    >
                    </PanelColorSettings>
                </PanelBody>
            </InspectorControls>,
            <div>
                <GridProjects projects={projects} projectAttributes={attributes} />
            </div>
        ]
    }
});

function GridProjects( attributes ) {
    if(attributes.projects === null) {
        return(
            <Placeholder 
               label="Project"
               instructions="Loading..."
               isColumnLayout 
           > 
         </Placeholder>
        );
    }
    else if(attributes.projects instanceof Array) {
        const projectsSelected = [];
        for( const project of attributes.projects ) {
            const singleProjectTypes = checkProjectTypes(project);

            //Check conditions, only one will be true
            if(singleProjectTypes.includes(attributes.projectAttributes.projectsType )) {
                projectsSelected.push( project );
            }
        }
        if(projectsSelected.length === 0) {
            return(
                <Placeholder 
                   label="Project"
                   instructions="No projects found."
                   isColumnLayout 
               > 
             </Placeholder>
            );
        }
        else {
            return (
                <div className="idcf-grid-projects-block" style={{gridTemplateColumns: `repeat(${attributes.projectAttributes.columnsInGrid}, minmax(0, 1fr)`}}>
                    {projectsSelected.map( (project, index) => <SingleGridProject project={project} projectAttributes={attributes.projectAttributes} projectIndex={index} />)}
                </div>
            );
        }
    }
}

function SingleGridProject( attributes ) {
    const projectTags = attributes.project._embedded["wp:term"] ? attributes.project._embedded["wp:term"].filter( tagsArray => tagsArray.length > 0 ) : [];
    const SelectedHeading = attributes.projectAttributes.projectsTitleSize;

    const singleProjectTypes = checkProjectTypes(attributes.project);
    let badgeText = '';

    if( attributes.projectAttributes.showBadge && singleProjectTypes.includes('successful') ) {
        badgeText = 'Successful';
    }
    if( attributes.projectAttributes.showBadge && singleProjectTypes.includes('failed') ) {
        badgeText = 'Failed';
    }

    return (
        <div className={`idcf-grid-projects-block-single idcf-grid-projects-block-single-${attributes.projectIndex + 1}`}>
            <div class="idcf-grid-projects-block-single-first">
                {attributes.projectAttributes.showImage && 
                <div className="idcf-grid-projects-block-single-img">
                    <img src={attributes.project.thumbnail} />
                </div>
                } 
                {attributes.projectAttributes.showImage && badgeText !== '' && <p style={{backgroundColor: attributes.projectAttributes.badgeColor}} className="idcf-grid-projects-block-single-badge">{badgeText}</p>}
                <ul className="idcf-grid-projects-block-single-tags">
                    {attributes.projectAttributes.showImage && projectTags[0] && projectTags[0].map(projectTagsArray => <li style={{backgroundColor: attributes.projectAttributes.categoryTagColor}}>{projectTagsArray.name}</li>)}
                </ul>
            </div>
            <SelectedHeading className="idcf-grid-projects-block-single-title" style={{color: attributes.projectAttributes.titleColor}}>{attributes.project.title.raw}</SelectedHeading>
            <p className="idcf-grid-projects-block-single-author" style={{color: attributes.projectAttributes.metaColor}}>by {attributes.project._embedded.author[0].name}</p>
            {attributes.projectAttributes.showExcerpt && 
                <div className="idcf-grid-projects-block-single-description" style={{color: attributes.projectAttributes.descriptionColor}} dangerouslySetInnerHTML={{ __html: attributes.project.ign_project_description }} />
            }
            <div className="idcf-grid-projects-block-single-progress-bar">
                <div style={{width: `${attributes.project.percentage}%`, backgroundColor: attributes.projectAttributes.barChartColor}}></div>
            </div>
            <div class="idcf-grid-projects-block-single-info" style={{color: attributes.projectAttributes.metaColor}}>
                <div className="idcf-grid-projects-block-single-info-percentage">
                    <span>{attributes.project.percentage}%</span>
                    <span>Funded</span>
                </div>
                <div className="idcf-grid-projects-block-single-info-total">
                    <span>{attributes.project.total}</span>
                    <span>Raised</span>
                </div>
                <div className="idcf-grid-projects-block-single-info-days">
                    <span>{attributes.project.days_left}</span>
                    <span>Days Left</span>
                </div>
            </div>
        </div>
    );
}

function checkProjectTypes(project) {
    const daysLeft = parseInt(project.days_left),
    percentage = parseInt(project.percentage),
    openType = project.ign_end_type,
    projectTypes = ['all'];

    if( daysLeft > 0 || openType === 'open' ) {
        projectTypes.push('active');
    }

    if( percentage >= 100 ) {
        projectTypes.push('successful');
    }

    if( percentage < 100 && daysLeft === 0 && openType === 'closed' ) {
        projectTypes.push('failed');
    }

    return projectTypes;
}