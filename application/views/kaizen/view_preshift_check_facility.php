<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<style type="text/css">
	#input-employee-name {
		font-size: 24px;
	}
	
	.yes-no-button {
		cursor: pointer;
		text-align: center;
		width: 50%;
		padding: 10px;
	}
	
	.yes-no-button:first-child {
		border-right: 1px solid grey;
	}
	
	.yes-button:hover {
		background-color: lightgreen;
	}
	
	.no-button:hover {
		background-color: #ff6961;
	}
	
	.yes-button-selected, .yes-button-selected:hover {
		background-color: green;
	}
	
	.no-button-selected, .no-button-selected:hover {
		background-color: red;
	}
	
	.question-area {
		background-color: lightgrey;
		text-align: center;
		padding: 10px !important;
	}
	
	.question-area img {
		width: 48px;
		float: left;
		margin-right: 10px;
	}
	
	.question-label {
		text-align: left;
		font-size: 16px;
		line-height: 18px;
		color: black;
		margin-bottom: 0;
	}
	
	.employee-checked-in {
		background-color: green;
		color: white !important;
	}
	
	.employee-flagged {
		background-color: red !important;
	}
</style>

<div class="page-header">
	<h3 style="text-align:center; color: #C52428;">
		<img src="<?php echo base_url('assets/data/kaizen/app/redstag-logo.png'); ?>" width="200"><br>
		PRESHIFT CHECK (<?php echo $facility_data['facility_name']; ?>) 
	</h3>
</div>

<div class="col-md-6">
	<div class="panel panel-default">
		<div class="panel-heading">
			<input type="text" class="form-control" id="input-employee-name" placeholder="Input Name">
		</div>
		<div class="list-group employee-list">
			
		</div>
	</div>
</div>

<div class="modal fade" id="preshift-check-modal" tabindex="-1" role="dialog" aria-labelledby="preshift-check-modal-label">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="preshift-check-modal-label">I Declare that I ...</h4>
      </div>
      <div class="modal-body">
		<form id="preshift-check-form">
		
			<input type="hidden" name="employee_id" class="pc-resp" id="pc-employee-id" value="">
	  
		<div class="row">
			<div class="col-md-3">
				<div class="panel panel-default">
					<div class="panel-body question-area">
						<img src="<?php echo base_url('assets/data/kaizen/app/preshift-check/facemask.png'); ?>">
						<p class="question-label">Wear a cloth face mask at work</p>
					</div>
					<table class="table">
						<tr>
							<td class="yes-no-button yes-button" id="yes-button-1" qid="1" response="Yes">Yes</td>
							<td class="yes-no-button no-button" id="no-button-1" qid="1" response="No">No</td>
						</tr>
					</table>
				</div>
			</div>
			<input type="hidden" name="1" class="pc-resp" id="pc-resp-1" value="">
			
			<div class="col-md-3">
				<div class="panel panel-default">
					<div class="panel-body question-area">
						<img src="<?php echo base_url('assets/data/kaizen/app/preshift-check/physical-distancing.png'); ?>">
						<p class="question-label">Maintain 6-feet physical distancing</p>
					</div>
					<table class="table">
						<tr>
							<td class="yes-no-button yes-button" id="yes-button-2" qid="2" response="Yes">Yes</td>
							<td class="yes-no-button no-button" id="no-button-2" qid="2" response="No">No</td>
						</tr>
					</table>
				</div>
			</div>
			<input type="hidden" name="2" class="pc-resp" id="pc-resp-2" value="">
			
			<div class="col-md-3">
				<div class="panel panel-default">
					<div class="panel-body question-area">
						<img src="<?php echo base_url('assets/data/kaizen/app/preshift-check/wash-hand.png'); ?>">
						<p class="question-label">Wash my hands regularly</p>
					</div>
					<table class="table">
						<tr>
							<td class="yes-no-button yes-button" id="yes-button-3" qid="3" response="Yes">Yes</td>
							<td class="yes-no-button no-button" id="no-button-3" qid="3" response="No">No</td>
						</tr>
					</table>
				</div>
			</div>
			<input type="hidden" name="3" class="pc-resp" id="pc-resp-3" value="">
			
			<div class="col-md-3">
				<div class="panel panel-default">
					<div class="panel-body question-area">
						<img src="<?php echo base_url('assets/data/kaizen/app/preshift-check/touch.png'); ?>">
						<p class="question-label">Avoid touching my face</p>
					</div>
					<table class="table">
						<tr>
							<td class="yes-no-button yes-button" id="yes-button-4" qid="4" response="Yes">Yes</td>
							<td class="yes-no-button no-button" id="no-button-4" qid="4" response="No">No</td>
						</tr>
					</table>
				</div>
			</div>
			<input type="hidden" name="4" class="pc-resp" id="pc-resp-4" value="">
		</div>
		<div class="row">
			<div class="col-md-3">
				<div class="panel panel-default">
					<div class="panel-body question-area">
						<img src="<?php echo base_url('assets/data/kaizen/app/preshift-check/policy.png'); ?>">
						<p class="question-label">Follow all worksite safety and cleaning policies</p>
					</div>
					<table class="table">
						<tr>
							<td class="yes-no-button yes-button" id="yes-button-5" qid="5" response="Yes">Yes</td>
							<td class="yes-no-button no-button" id="no-button-5" qid="5" response="No">No</td>
						</tr>
					</table>
				</div>
			</div>
			<input type="hidden" name="5" class="pc-resp" id="pc-resp-5" value="">
			
			<div class="col-md-3">
				<div class="panel panel-default">
					<div class="panel-body question-area">
						<img src="<?php echo base_url('assets/data/kaizen/app/preshift-check/isolate.png'); ?>">
						<p class="question-label">Not told to quarantine or isolate by a health care provider or the health department</p>
					</div>
					<table class="table">
						<tr>
							<td class="yes-no-button yes-button" id="yes-button-6" qid="6" response="Yes">Yes</td>
							<td class="yes-no-button no-button" id="no-button-6" qid="6" response="No">No</td>
						</tr>
					</table>
				</div>
			</div>
			<input type="hidden" name="6" class="pc-resp" id="pc-resp-6" value="">
			
			<div class="col-md-3">
				<div class="panel panel-default">
					<div class="panel-body question-area">
						<img src="<?php echo base_url('assets/data/kaizen/app/preshift-check/close.png'); ?>">
						<p class="question-label">Didn't have face-to-face contact for 10 or more minutes with someone who has COVID-19</p>
					</div>
					<table class="table">
						<tr>
							<td class="yes-no-button yes-button" id="yes-button-7" qid="7" response="Yes">Yes</td>
							<td class="yes-no-button no-button" id="no-button-7" qid="7" response="No">No</td>
						</tr>
					</table>
				</div>
			</div>
			<input type="hidden" name="7" class="pc-resp" id="pc-resp-7" value="">
			
			<div class="col-md-3">
				<div class="panel panel-default">
					<div class="panel-body question-area">
						<img src="<?php echo base_url('assets/data/kaizen/app/preshift-check/ill.png'); ?>">
						<p class="question-label">Not feeling ill and/or experiencing fever, cough, shortness of breath, new loss of sense of taste/smell, and vomiting or diarrhea within the past 24 hours</p>
					</div>
					<table class="table">
						<tr>
							<td class="yes-no-button yes-button" id="yes-button-8" qid="8" response="Yes">Yes</td>
							<td class="yes-no-button no-button" id="no-button-8" qid="8" response="No">No</td>
						</tr>
					</table>
				</div>
			</div>
			<input type="hidden" name="8" class="pc-resp" id="pc-resp-8" value="">
		</div>
		
		</form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="checkin-button">Check In</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="checking-in-modal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-body" style="color:white; font-size:30px; text-align:center; font-weight:bold;">
		Checking in...
	  </div>
	</div>
  </div>
</div>

<div class="modal fade" id="positive-feedback-modal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-body" style="background-color:green; color:white; font-size:30px; text-align:center; font-weight:bold;">
		WELCOME!
	  </div>
	</div>
  </div>
</div>

<div class="modal fade" id="negative-feedback-modal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-body" style="background-color:red; color:white; font-size:30px; text-align:center; font-weight:bold;">
		ENTRY DENIED
	  </div>
	</div>
  </div>
</div>
	  