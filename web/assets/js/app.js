var api = {
	programs: {
		findById: function(id) {
			return jQuery.get("/api/programs/" + id);	
		},
		findAll: function() {
			return jQuery.get("/api/programs");	
		},
		deleteById: function(id) {
			return jQuery.ajax({
				url: "/api/programs/" + id,
				type: "DELETE"
			});
		},
		add: function(data) {
			return jQuery.post( "/api/programs", data, "json");
		}
	}
};

function AppViewModel(data) {
    
    var self = this;
 	
    self.formShowInvalidMessage = ko.observable(false);

    self.program = ko.observableArray([]);
 
    self.addProgramToList = function(program) {
        // add new programs first in the list
        self.program.unshift(
        	new ProgramViewModel(program)	
		);
    };
 
    self.addProgram = function(form) {
    
    	var formArray = $(form).find("input, textarea").serializeArray();
    	var dateFields = ["date"];	
		var formValid = true;
		
		// check that form is valid
		_.forEach(formArray, function(item) {
			if(_.contains(dateFields, item.name)) {
				if(!moment(item.value).isValid()) {
					formValid = false;
				}
			} else {
				if(item.value.length == 0) {
					formValid = false;	
				}
			}	
		});

    	//convert to json format
    	var jsonFormat = {};
		_.forEach(formArray, function(item) {
			var value = item.value;
			if(item.name === "date") {
				value = moment(value).format();
			}	
			jsonFormat[item.name] = value;
		});

		if(formValid) {
			self.formShowInvalidMessage(false);
			// add new program to database
			api.programs.add(jsonFormat).done(function(data) {
				// get the newly created program from the database and add it to the list
				api.programs.findById(data.id).done(function(program) {
					self.addProgramToList(program);
				});
			});
		} else {
			self.formShowInvalidMessage(true);
		}
    };

    self.removeProgram = function() {
    	// get id from ProgramViewModel
    	var vm = this;
    	var id = vm.id;
    	// lets see if the user is sure about this
    	bootbox.confirm("Are you sure about this?", function() {
	    	// delete by id and remove from list
	    	api.programs.deleteById(id).done(function() {
	    		self.program.remove(vm);	
	    	});
    	});
    };

    self.populate = function(programs) {
    	_.forEach(programs, function(program) {
    		self.addProgramToList(program);
    	});
    };
}

function ProgramViewModel(data) {
    
    var self = this;
    self.id = data.id;
    self.name = data.name;
    self.date = data.date;
    self.startTime = data.start_time;
    self.leadtext = data.leadtext;
    self.bLine = data["b-line"];
    self.synopsis = data.synopsis;
    self.url = data.url;
};

// New app viewmodel
var App = new AppViewModel();

// Fetch all products and populate view
api.programs.findAll().done(function(data) {
	App.populate(data);
});

// Apply knockout bindings
ko.applyBindings(App);


// Init datepicker
$("#datetimepicker").datetimepicker({
	language: "sv"
});

$("#timepicker").datetimepicker({
	language: "sv",
	pickDate: false
});

