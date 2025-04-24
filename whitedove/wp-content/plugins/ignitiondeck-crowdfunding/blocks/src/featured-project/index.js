/**
 * Every block starts by registering a new block type definition.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */

import { registerBlockType } from '@wordpress/blocks';
import { useSelect } from '@wordpress/data';
import { SelectControl, RadioControl, Placeholder, PanelBody } from '@wordpress/components';
import { InspectorControls, PanelColorSettings } from '@wordpress/block-editor';
import IgnitionDeckIcon from '../icon.js'

registerBlockType("idcf-blocks/featured-project", {
    title: "Featured Project",
    category: "ignitiondeck",
    description: "An easy to place and configure block that allows a user to pick a specific project from their existing published.",
    icon: IgnitionDeckIcon,
    supports: {
		"html": false
	},
    textdomain: "idcf_blocks",
    attributes: {
		projectsSelected: {
			type: "integer",
			default: 0
		},
		titleColor: {
			type: "string",
			default: "#000"
		},
		descriptionColor: {
			type: "string",
			default: "#000"
		},
		buttonColor: {
			type: "string",
			default: "#3182CE"
		},
		buttonTextColor: {
			type: "string",
			default: "#FFF"
		},
		metaColor: {
			type: "string",
			default: "#000"
		},
		imageAlignment: {
			type: "string",
			default: "0"
		},
	},
    edit: ({attributes, setAttributes}) => {
        const colors = useSelect('core/block-editor').getSettings().colors;

        const projects = useSelect( ( select ) => {
            return select( 'core' ).getEntityRecords( 'postType', 'ignition_product', { per_page: -1 } );
        } );
   

        /* We already have the projects. Iterate over them to find the selected one */
        const onChangeProject = newProject => {
            for( const project of projects ) {
                if( project.id === parseInt(newProject) ) {
                    setAttributes({projectsSelected:parseInt(newProject)});
                    break;
                }
            }
        }

        return [
            <InspectorControls>
                <PanelBody>
                    <SelectControl 
                        label="Select Project"
                        value={ attributes.projectsSelected }
                        options={ 
                            projects instanceof Array
                            ? [].concat([{label: '', value: -1}], projects.map(({title, id}) => ({label: title.raw, value: id})))
                            : [{label: '', value: -1}]
                        }
                        onChange={ onChangeProject }
                    />
                </PanelBody>

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
                                value: attributes.buttonColor,
                                onChange:( colorValue ) => setAttributes( { buttonColor: colorValue } ),
                                label: 'Button Color',
                            },
                            {
                                value: attributes.buttonText,
                                onChange:( colorValue ) => setAttributes( { buttonTextColor: colorValue } ),
                                label: 'Button Text',
                            },
                            {
                                value: attributes.metaColor,
                                onChange: ( colorValue ) => setAttributes( { metaColor: colorValue } ),
                                label: 'Meta Color',
                            },
                        ] }
                    >
                </PanelColorSettings>

            <PanelBody title="Layout Settings">
                <RadioControl
                    label="Image Alignment"
                    selected={ attributes.imageAlignment }
                    options={ [
                        { label: 'Left', value: '0' },
                        { label: 'Right', value: '1' },
                    ] }
                    onChange={ ( value ) => setAttributes( { imageAlignment: value } ) }
                />
                </PanelBody>
            </InspectorControls>,

            <div>
                <Project projectBlockAttributes={attributes} projects={projects}/>
            </div>,
        ];
    }
});

/**
 * Checks the different possible scenarios
 */
function Project(attributes) {
    const {projects, projectBlockAttributes} = attributes;
    if(projects === null) {
        return(
            <Placeholder 
               label="Project"
               instructions="Loading..."
               isColumnLayout 
           > 
         </Placeholder>
        );
    }
    if(projects instanceof Array && projectBlockAttributes.projectsSelected > 0) {
        for( const project of projects ) {
            if( project.id === projectBlockAttributes.projectsSelected ) {
                return(<ProjectHTML projectAttributes={project} projectBlockAttributes={projectBlockAttributes}/>);
            }
        }
    }
    if(projects instanceof Array && projects.length === 0) {
        return(
            <Placeholder 
               label="Project"
               instructions="Your website doesn't have any projects."
               isColumnLayout 
           > 
         </Placeholder>
        );
    }
    return(
        <Placeholder 
            label="Project"
            instructions="Select a project from the right sidebar"
            isColumnLayout 
        > 
        </Placeholder>
     );
}


function ProjectHTML(attributes) {
    // SVG icons from https://www.svgrepo.com/ 
    const {title, ign_project_description, thumbnail, total, goal, pledges, days_left, link} = attributes.projectAttributes;
    const {titleColor, descriptionColor, imageAlignment, metaColor, buttonColor, buttonTextColor} = attributes.projectBlockAttributes;

    return (
        <div className={"idcf-featured-project-block"}>
            <div className={"idcf-featured-project-block-first-row"}>
                <h2 style={{color:titleColor}}>{title.raw}</h2>
                <p style={{color:descriptionColor}}>{ign_project_description}</p>
            </div>

            <div className={"idcf-featured-project-block-second-row"}>
                <div className="idcf-featured-project-block-single-image" style={{order:imageAlignment}}>
                    <img src={thumbnail} />
                </div>

                <div className={"idcf-featured-project-meta"}>
                    <div className="idcf-featured-project-block-single-total">
                        <h3 style={{color:metaColor}}>{total}</h3>
                        <p style={{color:metaColor}}>Pledged of {goal}</p>
                    </div>
                    <div className="idcf-featured-project-block-single-pledges">
                        <h3 style={{color:metaColor}}>{pledges}</h3>
                        <p style={{color:metaColor}}>backers</p>
                    </div>
                    <div className="idcf-featured-project-block-single-days">
                        <h3 style={{color:metaColor}}>{days_left}</h3>
                        <p style={{color:metaColor}}>days to go</p>
                    </div>
                    <a className={"idcf-action-button idcf-featured-project-block-single-support"} style={{backgroundColor:buttonColor,color: buttonTextColor}} href={link}>Back this project</a>
                    <div className={"idcf-featured-project-share"}>
                        <p>Share on Social Media</p>
                        <div>
                            <div className="idcf-featured-project-block-single-twitter">
                                <a href="javascript:void(0)">
                                    <svg viewBox="0 0 512 512" height="30" width="30" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M459.37 151.716c.325 4.548.325 9.097.325 13.645 0 138.72-105.583 298.558-298.558 298.558-59.452 0-114.68-17.219-161.137-47.106 8.447.974 16.568 1.299 25.34 1.299 49.055 0 94.213-16.568 130.274-44.832-46.132-.975-84.792-31.188-98.112-72.772 6.498.974 12.995 1.624 19.818 1.624 9.421 0 18.843-1.3 27.614-3.573-48.081-9.747-84.143-51.98-84.143-102.985v-1.299c13.969 7.797 30.214 12.67 47.431 13.319-28.264-18.843-46.781-51.005-46.781-87.391 0-19.492 5.197-37.36 14.294-52.954 51.655 63.675 129.3 105.258 216.365 109.807-1.624-7.797-2.599-15.918-2.599-24.04 0-57.828 46.782-104.934 104.934-104.934 30.213 0 57.502 12.67 76.67 33.137 23.715-4.548 46.456-13.32 66.599-25.34-7.798 24.366-24.366 44.833-46.132 57.827 21.117-2.273 41.584-8.122 60.426-16.243-14.292 20.791-32.161 39.308-52.628 54.253z"></path>
                                    </svg>
                                </a>
                            </div>

                            <div className="idcf-featured-project-block-single-facebook">
                                <a href="javascript:void(0)">
                                    <svg fill="currentColor" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 60.734 60.733"><g id="SVGRepo_bgCarrier" strokeWidth="0"></g><g id="SVGRepo_tracerCarrier" strokeLinecap="round" strokeLinejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <path d="M57.378,0.001H3.352C1.502,0.001,0,1.5,0,3.353v54.026c0,1.853,1.502,3.354,3.352,3.354h29.086V37.214h-7.914v-9.167h7.914 v-6.76c0-7.843,4.789-12.116,11.787-12.116c3.355,0,6.232,0.251,7.071,0.36v8.198l-4.854,0.002c-3.805,0-4.539,1.809-4.539,4.462 v5.851h9.078l-1.187,9.166h-7.892v23.52h15.475c1.852,0,3.355-1.503,3.355-3.351V3.351C60.731,1.5,59.23,0.001,57.378,0.001z"></path> </g> </g></svg>
                                </a>
                            </div>

                            <div className="idcf-featured-project-block-single-linkedin">
                                <a href="javascript:void(0)">
                                    <svg fill="currentColor" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32"><g id="SVGRepo_bgCarrier" strokeWidth="0"></g><g id="SVGRepo_tracerCarrier" strokeLinecap="round" strokeLinejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <path d="M17.303,14.365c0.012-0.015,0.023-0.031,0.031-0.048v0.048H17.303z M32,0v32H0V0H32L32,0z M9.925,12.285H5.153v14.354 h4.772V12.285z M10.237,7.847c-0.03-1.41-1.035-2.482-2.668-2.482c-1.631,0-2.698,1.072-2.698,2.482 c0,1.375,1.035,2.479,2.636,2.479h0.031C9.202,10.326,10.237,9.222,10.237,7.847z M27.129,18.408c0-4.408-2.355-6.459-5.494-6.459 c-2.531,0-3.664,1.391-4.301,2.368v-2.032h-4.77c0.061,1.346,0,14.354,0,14.354h4.77v-8.016c0-0.434,0.031-0.855,0.157-1.164 c0.346-0.854,1.132-1.746,2.448-1.746c1.729,0,2.418,1.314,2.418,3.246v7.68h4.771L27.129,18.408L27.129,18.408z"></path> </g> </g></svg>
                                </a>
                            </div>

                            <div className="idcf-featured-project-block-single-pinterest">
                                <a href="javascript:void(0)">
                                    <svg fill="currentColor" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 455.731 455.731"><g id="SVGRepo_bgCarrier" strokeWidth="0"></g><g id="SVGRepo_tracerCarrier" strokeLinecap="round" strokeLinejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M0,0v455.731h455.731V0H0z M384.795,288.954c-35.709,91.112-131.442,123.348-203.22,100.617 c5.366-13.253,11.473-26.33,15.945-39.943c4.492-13.672,7.356-27.878,10.725-41.037c2.9,2.44,5.814,5.027,8.866,7.439 c15.861,12.535,33.805,13.752,52.605,9.232c19.977-4.803,35.764-16.13,47.455-32.78c19.773-28.16,26.751-60.019,21.972-93.546 c-4.942-34.668-25.469-59.756-57.65-72.389c-48.487-19.034-94.453-12.626-134.269,22.259 c-30.622,26.83-40.916,72.314-26.187,107.724c5.105,12.274,13.173,21.907,25.38,27.695c6.186,2.933,8.812,1.737,10.602-4.724 c0.133-0.481,0.295-0.955,0.471-1.422c3.428-9.04,2.628-16.472-3.472-25.199c-11.118-15.906-9.135-34.319-3.771-51.961 c10.172-33.455,40.062-55.777,75.116-56.101c9.39-0.087,19.056,0.718,28.15,2.937c27.049,6.599,44.514,27.518,46.264,55.253 c1.404,22.242-2.072,43.849-11.742,64.159c-4.788,10.055-11.107,18.996-20.512,25.325c-8.835,5.945-18.496,8.341-28.979,5.602 c-14.443-3.774-22.642-16.95-18.989-31.407c3.786-14.985,8.685-29.69,12.399-44.69c1.57-6.344,2.395-13.234,1.75-19.696 c-1.757-17.601-18.387-25.809-33.933-17.216c-10.889,6.019-16.132,16.079-18.564,27.719c-2.505,11.992-1.292,23.811,2.61,35.439 c0.784,2.337,0.9,5.224,0.347,7.634c-7.063,30.799-14.617,61.49-21.306,92.369c-1.952,9.011-1.589,18.527-2.239,27.815 c-0.124,1.78-0.018,3.576-0.018,5.941C86.223,350.919,37.807,262.343,68.598,172.382C99.057,83.391,197.589,36.788,286.309,69.734 C375.281,102.774,419.287,200.947,384.795,288.954z"></path> </g></svg>
                                </a>
                            </div>
                        </div>
                    </div>
                    <p>
                        {endTypeText( attributes.projectAttributes )}
                    </p>
                </div>
            </div>
        </div>
    );
}

function endTypeText( projectAttributes ) {
   const {ign_end_type, days_left} = projectAttributes;
   let endTypeText = "";

   if( ign_end_type === "closed" && days_left > 0 ) {
       endTypeText = `This project ends in ${days_left} days.`;
   }
   else if( days_left <= 0 ) {
       endTypeText = "This project has ended.";
   }
   return endTypeText;
}
