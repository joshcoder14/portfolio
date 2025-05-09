/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-i18n/
 */
 import { __ } from '@wordpress/i18n';

 /**
  * React hook that is used to mark the block wrapper element.
  * It provides all the necessary props like the class name.
  *
  * @see https://developer.wordpress.org/block-editor/packages/packages-block-editor/#useBlockProps
  */
 import { useBlockProps } from '@wordpress/block-editor';
 
 /**
  * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
  * Those files can contain any CSS code that gets applied to the editor.
  *
  * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
  */
 import './editor.scss';
 
 /**
  * The edit function describes the structure of your block in the context of the
  * editor. This represents what the editor will render when the block is used.
  *
  * @see https://developer.wordpress.org/block-editor/developers/block-api/block-edit-save/#edit
  *
  * @return {WPElement} Element to render.
  */
 import { useSelect } from '@wordpress/data';
 import { SelectControl, RadioControl, Placeholder, Panel, PanelBody } from '@wordpress/components';
 import { InspectorControls, PanelColorSettings } from '@wordpress/block-editor';

 export default function Edit( { attributes, setAttributes } ) {
	 const blockProps = useBlockProps();
	 const colors = useSelect('core/block-editor').getSettings().colors;

	 const projects = useSelect( ( select ) => {
		 return select( 'core' ).getEntityRecords( 'postType', 'ignition_product');
	 } );
	
	var social = {
		'twitter': 'javascript:void(0)',
		'facebook': 'javascript:void(0)',
		'email': 'javascript:void(0)',
 	};

	const user = useSelect( ( select ) => {
		return select( 'core' ).getCurrentUser();
	} );

	if( Object.entries(user).length !== 0 ) {
		if( user['socialLinks']['twitter'] ) {
			social.twitter = user['socialLinks']['twitter'];
			setAttributes({userTwitter:social.twitter});
		}

		if( user['socialLinks']['facebook'] ) {
			social.facebook = user['socialLinks']['facebook'];
			setAttributes({userFacebook:social.facebook});
		}

		if( user['socialLinks']['email'] ) {
			social.email = user['socialLinks']['email'];
			setAttributes({userEmail:social.email});
		}
	}

	let noProjects = false;
	 if( projects && projects.length > 0 && attributes.projectOptionsSelected.length === 1 ) {
		const tmpOptionsArray = [];
		if( attributes.projectsSelected === -1 ) {
			tmpOptionsArray[0] = {value: 0, label: 'Select a Project'};
		}

		for( const value of projects ) {
			const project= {
				label: value.title.raw,
				value: value.id
			}
			tmpOptionsArray.push(project);
		}
		setAttributes({projectOptionsSelected:tmpOptionsArray});
	 }
	 else if( projects && projects.length === 0 ) {
		noProjects = true;
	 }

	 const onChangeProject = newProject => {
		 for( const project of projects ) {
			 if( project.id === parseInt(newProject) ) {
				setAttributes({projectsSelected:parseInt(newProject)});
				setAttributes({projectTitle:project.title.raw});
				setAttributes({projectDescription:project.ign_project_description});
				setAttributes({projectTotal:project.total});
				setAttributes({projectGoal:project.goal});
				setAttributes({projectPledges:project.pledges});
				setAttributes({projectDaysLeft:project.days_left});
				setAttributes({projectUrl:project.link});
				setAttributes({projectThumbnail:project.thumbnail});
				setAttributes({endType:project.end_type});
				setAttributes({projectOptionsSelected:[{ value: project.id, label: project.title.raw }]});
				break;
			 }
		 }
	 }
 
	 const titleStyle = {
		 color: attributes.titleColor
	 }
 
	 const descriptionStyle = {
		 color: attributes.descriptionColor
	 }
 
	 const buttonStyle = {
		 color: attributes.buttonText,
		 backgroundColor: attributes.buttonColor
	 }

	 const metaStyle = {
		color: attributes.metaColor
	}
 
	 return [
		 <InspectorControls>
			 <PanelBody>
				 <SelectControl 
					 label="Select Project"
					 value={ attributes.projectsSelected }
					 options={ attributes.projectOptionsSelected }
					 onChange={ onChangeProject }
				 />
			 </PanelBody>
 
			 <PanelColorSettings
					 title={ __( 'Color Settings' ) }
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
							 onChange:( colorValue ) => setAttributes( { buttonText: colorValue } ),
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
 
		 <div { ...blockProps }>
			 <Project projectAttributes={attributes} projectTitleStyle={titleStyle} projectDescriptionStyle={descriptionStyle} projectButtonStyle={buttonStyle} projectMetaStyle={metaStyle} socialLinks={social} noProjects={noProjects}/>
		 </div>,
	 ];
 }

 function Project(attributes) {
	 if( attributes.projectAttributes.projectsSelected !== -1 ) {
		return (
			<div className={"idcf-featured-project-block"}>
				<div className={"idcf-featured-project-block-first-row"}>
					<h2 style={attributes.projectTitleStyle}>{attributes.projectAttributes.projectTitle}</h2>
					<p style={attributes.projectDescriptionStyle}>{attributes.projectAttributes.projectDescription}</p>
				</div>
	
				<div className={"idcf-featured-project-block-second-row"}>
					<div style={{order:attributes.projectAttributes.imageAlignment}}>
						<img src={attributes.projectAttributes.projectThumbnail} />
					</div>
	
					<div className={"idcf-featured-project-meta"}>
						<div>
							<h3 style={attributes.projectMetaStyle}>{attributes.projectAttributes.projectTotal}</h3>
							<p style={attributes.projectMetaStyle}>Pledged of {attributes.projectAttributes.goal}</p>
						</div>
						<div>
							<h3 style={attributes.projectMetaStyle}>{attributes.projectAttributes.projectPledges}</h3>
							<p style={attributes.projectMetaStyle}>backers</p>
						</div>
						<div>
							<h3 style={attributes.projectMetaStyle}>{attributes.projectAttributes.projectDaysLeft}</h3>
							<p style={attributes.projectMetaStyle}>days to go</p>
						</div>
						<a className={"idcf-action-button"} style={attributes.projectButtonStyle} href={attributes.projectAttributes.projectUrl}>Back this project</a>
						<div className={"idcf-featured-project-share"}>
							<p>Share on Social Media</p>
							<div>
								<div>
									<a href={attributes.socialLinks.twitter}>
										<svg viewBox="0 0 512 512" height="30" width="30" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M459.37 151.716c.325 4.548.325 9.097.325 13.645 0 138.72-105.583 298.558-298.558 298.558-59.452 0-114.68-17.219-161.137-47.106 8.447.974 16.568 1.299 25.34 1.299 49.055 0 94.213-16.568 130.274-44.832-46.132-.975-84.792-31.188-98.112-72.772 6.498.974 12.995 1.624 19.818 1.624 9.421 0 18.843-1.3 27.614-3.573-48.081-9.747-84.143-51.98-84.143-102.985v-1.299c13.969 7.797 30.214 12.67 47.431 13.319-28.264-18.843-46.781-51.005-46.781-87.391 0-19.492 5.197-37.36 14.294-52.954 51.655 63.675 129.3 105.258 216.365 109.807-1.624-7.797-2.599-15.918-2.599-24.04 0-57.828 46.782-104.934 104.934-104.934 30.213 0 57.502 12.67 76.67 33.137 23.715-4.548 46.456-13.32 66.599-25.34-7.798 24.366-24.366 44.833-46.132 57.827 21.117-2.273 41.584-8.122 60.426-16.243-14.292 20.791-32.161 39.308-52.628 54.253z"></path>
										</svg>
									</a>
								</div>
	
								<div>
									<a href={attributes.socialLinks.facebook}>
										<svg viewBox="0 0 448 512" height="30" width="30" fill="currentColor" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMinYMin meet"><path d="M448 56.7v398.5c0 13.7-11.1 24.7-24.7 24.7H309.1V306.5h58.2l8.7-67.6h-67v-43.2c0-19.6 5.4-32.9 33.5-32.9h35.8v-60.5c-6.2-.8-27.4-2.7-52.2-2.7-51.6 0-87 31.5-87 89.4v49.9h-58.4v67.6h58.4V480H24.7C11.1 480 0 468.9 0 455.3V56.7C0 43.1 11.1 32 24.7 32h398.5c13.7 0 24.8 11.1 24.8 24.7z"></path></svg>
									</a>
								</div>
	
								<div>
									<svg viewBox="0 0 448 512" height="30" width="30" fill="currentColor" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMinYMin meet"><path d="M416 32H31.9C14.3 32 0 46.5 0 64.3v383.4C0 465.5 14.3 480 31.9 480H416c17.6 0 32-14.5 32-32.3V64.3c0-17.8-14.4-32.3-32-32.3zM135.4 416H69V202.2h66.5V416zm-33.2-243c-21.3 0-38.5-17.3-38.5-38.5S80.9 96 102.2 96c21.2 0 38.5 17.3 38.5 38.5 0 21.3-17.2 38.5-38.5 38.5zm282.1 243h-66.4V312c0-24.8-.5-56.7-34.5-56.7-34.6 0-39.9 27-39.9 54.9V416h-66.4V202.2h63.7v29.2h.9c8.9-16.8 30.6-34.5 62.9-34.5 67.2 0 79.7 44.3 79.7 101.9V416z"></path></svg>
								</div>
	
								<div>
									<a href={`mailto:${attributes.socialLinks.email}`}>
										<svg viewBox="0 0 24 24" height="30" width="30" fill="none" stroke="currentColor" xmlns="http://www.w3.org/2000/svg" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
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
	 else if(attributes.noProjects === true) {
		return(
			<Placeholder 
				label="Project"
				instructions="There is any project for select"
				isColumnLayout 
			> 
			</Placeholder>
		 );
	 }
	 else {
		 return(
			<Placeholder 
				label="Project"
				instructions="Select a project from the right sidebar"
				isColumnLayout 
			> 
			</Placeholder>
		 );
	 }
 }

 function endTypeText( projectAttributes ) {
	const endType = projectAttributes.endType,
	daysLeft = projectAttributes.projectDaysLeft;
	let endTypeText = "";

	if( endType === "closed" && daysLeft > 0 ) {
		endTypeText = `This project ends in ${projectAttributes.projectDaysLeft} days.`;
	}
	else if( daysLeft <= 0 ) {
		endTypeText = "This project has ended.";
	}
	return endTypeText;
 }
 