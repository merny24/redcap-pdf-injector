<?php
namespace STPH\pdfInjector;

use RCView;


//	Use \Files::uploadFile($_FILES[$key]) to upload file into $edoc
//	Check out https://github.com/vanderbilt-redcap/big-data-import/blob/master/saveData.php:56

# 1. Upload, validate and scan File. Save if successful to storage, retrieve edoc id 
# and save as new injection into module data json

# 2. Add title, description, and field associations

# Submit File to Check: Check if file is valid, save temporarly
# Save edoc to database, return ready for scan
# Scan file, return field data to client, save field data to database
# Files::getEdocName
# https://github.com/mozilla/pdf.js

$module->initModule();

renderPageTitle('<i class="fas fa-syringe"></i> PDF Injector');
print '<div style="width:950px;max-width:950px;" class="d-none d-md-block mt-3 mb-2">'.$module->tt("injector_1").'</div>';

?>

<!-- ALERTS TABLE -->
<div style="width:950px;max-width:950px;">
			<div class="mb-1 clearfix">
				<button id='addNewInjection' type="button" class="btn btn-sm btn-success float-left"><i class="fas fa-plus"></i> <?= $module->tt("injector_2") ?></button>				
				<div class="float-right mt-2 mr-1">
				</div>
			</div>
            <?php if (count($module->injections) > 0) : ?>
				<table class="table table-bordered table-hover email_preview_forms_table" id="injectionsPreview" style="display:none;width:100%;table-layout: fixed;">
					<thead>
						<tr class="table_header d-none">
							<th></th>
							<th></th>
							<th></th>
							<th></th>
						</tr>
					</thead>
					<tbody>
					<?php

					$injection_number = 0;
					// Loop through all injections
					foreach ($module->injections as $injection_id=>$attr) 
					{
						$injection_number++;
						$fields = $attr["fields"];

						$description = "<b class=\"fs14\"><i class=\"fas fa-info-circle\"></i></b> <span class=\"boldish\">{$attr['description']}</span>";
						$fieldInfo = "<b class=\"fs14\"><i class=\"fa fa-th-list\"></i></b> <span class=\"boldish\">Has a total of ".count($fields)." fields to  inject:</span>";
						$fieldList= "";

						foreach ($fields as $key => $value) {
							if($value == "") {
								$fieldList .= "<li>{$key}: <span style=\"color:red;\">not assigned to any variable</span></li>";
							} else {
								$fieldList .= "<li>{$key}: <span class=\"code\" style=\"font-size:85%;\">[{$value}]</span></li>";
							}
						}

						if(!isset($attr["doc_id"])) {
							$thumbnailPath = $module->getUrl("img/PDF_file_icon.svg");
						} else {
							$thumbnailPath = $module->base64FromId($attr["doc_id"]);
						}

						$activityBox = '<div class="clearfix">
											<div class="float-left boldish" style="color:#6320ac;width:90px;">
												<i class="fs14 fas fa-tachometer-alt"></i> '.$lang['alerts_103'].'
											</div>
											<div class="float-left">Activities</div>
										</div>';

						
						$injectionTitle = (trim($attr['title']) == '') ? '' : $lang['colon'].'<span class="font-weight-normal ml-1">'.RCView::escape($attr['title']).'</span>';						
						$formName = '<div class="clearfix" style="margin-left: -11px;">
										<div style="max-width:340px;" class="card-header alert-num-box float-left text-truncate"><i class="fas fa-syringe fs13" style="margin-right:5px;"></i>PDF Injection #'.$injection_number.$injectionTitle.'</div>
										<div class="btn-group nowrap float-left mb-1 ml-2" role="group">
										  <button style="color:#0061b5;" type="button" class="btn btn-link fs13 py-1 pl-1 pr-2" onclick="STPH_pdfInjector.editInjection('.$injection_id.', '.$injection_number.');">
											<i class="fas fa-pencil-alt"></i> '.$lang['global_27'].'
										  </button>
										  <button style="color:#0061b5;" type="button" class="btn btn-link fs13 py-1 pl-1 pr-2"  onclick="STPH_pdfInjector.deleteInjection('.$injection_id.', '.$injection_number.');">
											<i class="fas fa-trash"></i> Delete
										  </button>										  
										  </div>
										</div>
									  </div>';
						// Output row
						$injections = "<tr>";						
						$injections .= "<td class='pt-0 pb-4' style='border-right:0;' data-order='".$injection_number."'>
										".$formName."
										<div class='card mt-3'>
											<div class='card-body p-2'>
												<div id=\"injection-descrip{$key}\" class=\"mb-1 trigger-descrip\">{$description}</div>
												<div class=\"mt-1\" style=\"color:green;\">{$fieldInfo}</div>
												<ol  class=\"mt-1\" style=\"padding-left:20px;\">".$fieldList."</ol>
											</div>
										</div>
										<div class='card mt-3'>
											<div class='card-body p-2'>{$activityBox}</div>
										</div>																		
										</td>";
						
						$injections .= "<td class='pt-3 pb-4' style='width:250px;border-left:0;'>
										<div class='card'>
										<div class='card-header bg-light py-1 px-3 clearfix' style='color:#004085;background-color:#d5e3f3 !important;'>
											<div class='float-left'><i class='fas fa-file-pdf'></i> PDF</div>
											<div class=\"btn-group nowrap float-right\" role=\"group\">
											<div class=\"btn-group\" role=\"group\">
												<button id=\"btnGroupDrop2\" type=\"button\" class=\"btn btn-link fs12 p-0 dropdown-toggle\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\">
												{$lang['design_699']}
												</button>
												<div class=\"dropdown-menu\" aria-labelledby=\"btnGroupDrop2\">
												<a class=\"dropdown-item\" href=\"#\" onclick=\"previewEmailAlert('$injection_id','$injection_number')\"><i class=\"far fa-envelope\"></i> {$module->tt("injector_3")}</a>
												<a class=\"dropdown-item\" href=\"#\" onclick=\"previewEmailAlertRecord('$injection_id','$injection_number')\"><i class=\"far fa-envelope\"></i> {$module->tt("injector_4")}</a>
												</div>
											</div>
											</div>
										</div>
										<div style=\"text-align:center\" class='card-body p-0'>
											<img class=\"my-shadow\" style=\"padding:15px;margin-top:15px;margin-bottom:15px;\"  width=\"125\" src=\"{$thumbnailPath}\" />
										</div>
									</div>
									</td>";

						$injections .= "<td style='display:none;'></td>";
						$injections .= "<td style='display:none;'></td>";
						$injections .= "</tr>";
						echo $injections;
						
					}					
					?>
					</tbody>
					</table>
			<?php endif; ?>
	</div>


	<div class="col-md-12">
		<form class="form-horizontal" action="" method="post" id="saveAlert">
			<div class="modal fade" id="external-modules-configure-modal" name="external-modules-configure-modal" data-module="" tabindex="-1" role="dialog" data-toggle="modal" data-backdrop="static" data-keyboard="true">
				<div class="modal-dialog" role="document" style="max-width: 950px !important;">
					<div class="modal-content">

						<div class="modal-header py-2">
							<button type="button" class="py-2 close closeCustomModal" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
							<h4 id="add-edit-title-text" class="modal-title form-control-custom"></h4>
						</div>

						<div class="modal-body pt-2">
							<div id="errMsgContainerModal" class="alert alert-danger col-md-12" role="alert" style="display:none;margin-bottom:20px;"></div>

							<!-- Modal Explanation Text -->
							<div class="mb-2">
								<?=$module->tt("injector_5")?>
							</div>

							<!-- STEP 1: Choose a valid PDF -->
							<section>
								<div class="form-control-custom-title clearfix mb-2">
									<div class="boldish fs14" style="margin-top:2px;"><i class="fas fa-file-upload"></i> <?= $module->tt("injector_10") ?></div>
								</div>

								<!-- File Input: if file has not been submitted yet or is not valid -->
								<div class="form-group">
									<label class="fs14 boldish"><?=$module->tt("injector_8")?></label>
									<div class="custom-file mb-3">
										<input id="file" name="file" type="file" class="custom-file-input">
										<label id="fileLabel" class="custom-file-label" >Choose file...</label>
										<div id="fpdm-error" class="invalid-feedback d-none"></div>
										<div id="fpdm-success" class="valid-feedback">Test</div>

										<div id="invalid-scan" class="invalid-feedback d-none">The file you have submitted could not be scanned.</div>
										<div id="invalid-fields" class="invalid-feedback d-none">The file you have submitted has no form fields.</div>
									</div>
								</div>			
							</section>
							<!-- STEP 2: Assign Fields to variables -->
							<section id="step-2" class="disabled">
								
								<div class="form-control-custom-title clearfix mb-2">
									<div class="boldish fs14" style="margin-top:2px;"><i class="fas fa-th-list"></i> <?= $module->tt("injector_11") ?></div>
								</div>

								<div class="disabled-message">
									<p class="text-secondary px-4 py-4">Please complete Step 1 to continue with this step.</p>
								</div>

								<div id="field-to-variable-map" class="form-group">

									<label class="fs14 boldish"><?=$module->tt("injector_14")?></label>

									<div id="load-output"></div>
								
								</div>
							</section>
							<!-- STEP 3: Set title and description -->
							<section id="step-3" class="disabled">
								<div class="form-control-custom-title clearfix mb-2">
									<div class="boldish fs14" style="margin-top:2px;"><i class="fas fa-tag"></i> <?= $module->tt("injector_12") ?></div>
								</div>								

								<!-- Title and Description Input-->							
								<div class="form-group">
									<label class="fs14 boldish"><?=$module->tt("injector_6")?></label>
									<input type="text" name="injection-title" class="form-control" placeholder="New Injection Title">
								</div>
								<div class="form-group">
									<label class="fs14 boldish"><?=$module->tt("injector_7")?></label>
									<textarea name="injection-description" class="form-control" rows="2" placeholder="Describe your PDF Injection with a few words.."></textarea>									
								</div>										
							</section>

						</div>

						<div class="modal-footer">
							<button data-toggle="modal" class="btn btn-rcgreen" id="btnModalsaveAlert" onclick="return false;"><?=$lang['designate_forms_13']?></button>
							<button class="btn btn-defaultrc" id="btnCloseCodesModal" data-dismiss="modal" onclick="return false;"><?=$lang['global_53']?></button>
						</div>

					</div>
				</div>
			</div>
		</form>
	</div>
<?php 