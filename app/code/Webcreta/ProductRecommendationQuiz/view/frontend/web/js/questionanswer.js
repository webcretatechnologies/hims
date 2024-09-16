require(['jquery'], function($) {

    $(document).ready(function() {

        // Options selected and unselected on click
        $(document).on('click', '.option', function() {
            if ($(this).hasClass('selected')) {
                $(this).removeClass('selected');
                $(this).find('input[type="checkbox"]').prop('checked', false);
            } else {
                $(this).addClass('selected');
                $(this).find('input[type="checkbox"]').prop('checked', true);
            }

            var radioButton = $(this).find('input[type="radio"]');
            if (radioButton.length) {
                $('.option').find('input[type="radio"]').prop('checked', false);

                radioButton.prop('checked', true);

                $('.option').removeClass('selected');
                $(this).addClass('selected');
            }
        });

        // File selected img showing 
        $(".options").on("change", "#fileInput", function(event) {

            var file = event.target.files[0];

            if (file) {
                var reader = new FileReader();

                reader.onload = function(e) {
                    $('.selected-image').empty();
                    $('#quiz-container .options').append(
                        '<div class="selected-image">' +
                        '<img src="' + e.target.result + '">' +
                        '</div>'
                    );
                };
                reader.readAsDataURL(file);
            }
        });

        getCustomerOldData();

        function getCustomerOldData() {
            $.ajax({
                url: getOldQuestionUrl,
                method: 'POST',
                dataType: 'json',
                data: {
                    categoryAttributeValue: categoryAttributeValue,
                },
                showLoader: true,
                success: function(response) {
                    localStorage.setItem(`question_backword${categoryId}`, false);

                    if (Array.isArray(response.data)) {
                      const attributeSetId = categoryAttributeValue;
                      const dataLength = response.data.length;
                      const transformedData = response.data
                      .filter(item => item.key != null && item.value != null)
                      .map((item, index) => ({
                        id: item.id,
                        currentQuestionId: item.key.toString(),
                        selectedOptionId: item.value.toString(),
                        attributeSetId,
                      }));
                    
                      localStorage.setItem(`question_back-${categoryId}`, JSON.stringify(transformedData.slice(0, -1)));
                      localStorage.setItem(`question_sets-${categoryId}`, JSON.stringify(transformedData));
                    
                      if (dataLength > 0) {
                        const lastElement = response.data[dataLength - 1];
                        const lastKey = lastElement.key;
                        const lastValue = Array.isArray(lastElement.value) ? lastElement.value.join(', ') : lastElement.value.toString();
                    
                        localStorage.setItem(`question_id_${categoryId}`, lastKey);
                        localStorage.setItem(`question_value_${categoryId}`, lastValue);
                      } else {
                        console.warn("Response.data array is empty");
                      }
                    } else {
                      console.error("Response.data is not an array. Unable to transform data.");
                    }
                    
                    fetchQuestionData();
                  },
                  error: function(xhr, status, error) {
                    console.log("Error occurred while fetching question data:", error);
                  }
            });
        }

        // On Load get First Question Else get session question data
        function fetchQuestionData() {
            var currentQuestionId = localStorage.getItem('question_id_' + categoryId);
            var selectedOptionId = localStorage.getItem('question_value_' + categoryId);

            if (currentQuestionId !== 'undefined' && selectedOptionId !== 'undefined' && currentQuestionId !== '' && currentQuestionId !== null) {
                var attributeSetId = categoryAttributeValue;
                ajaxCallForGetBackQuestion(currentQuestionId, selectedOptionId, attributeSetId);
            }else {
                $.ajax({
                    url: getquestion,
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        categoryAttributeValue: categoryAttributeValue
                    },success: function(response) {
                        if (response.success !== false) {
                            const questionElement = $('.question');
                            const optionsContainer = $('.options');
                        
                            localStorage.setItem('nextQuestionId-' + categoryId, response.data.question_id);
                            questionElement.attr('data-question-id', response.data.question_id);
                            questionElement.find('p').text(response.data.question);
                            optionsContainer.empty();
                        
                            const createOptionElement = (type, option) => {
                                return `
                                    <div class="option" data-option-id="${option.value || ''}">
                                        <input required type="${type}" name="${type === 'checkbox' ? 'option[]' : 'option'}" id="option_${option.value || type}"  value="${option.value || ''}" 
                                            data-group-id="${option.groupid || ''}">
                                        <label for="option_${option.value || type}">${option.label || ''}</label>
                                    </div>
                                `;
                            };
                        
                            switch (response.data.type) {
                                case 'text':
                                    optionsContainer.append(`
                                        <div class="option">
                                            <input required type="text" id="text_field" name="text_field">
                                        </div>
                                    `);
                                    break;
                        
                                case 'date':
                                    optionsContainer.append(`
                                        <div class="option">
                                            <input required type="date" id="date" name="date">
                                        </div>
                                    `);
                                    break;
                        
                                case 'multiselect':
                                    response.data.options.forEach(option => {
                                        optionsContainer.append(createOptionElement('checkbox', option));
                                    });
                                    break;
                        
                                default:
                                    response.data.options.forEach(option => {
                                        optionsContainer.append(createOptionElement('radio', option));
                                    });
                                    break;
                            }
                        }
                        
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                    }
            }   );
            }
        }

        // On click Reset Button
        $('#reset-question').on('click', function() {
            var attributeSetId = categoryAttributeValue;

            localStorage.setItem('question_id_' + categoryId, '');
            localStorage.setItem('question_value_' + categoryId, '');
            localStorage.setItem('question_sets-' + categoryId, '');
            localStorage.setItem('groupIds' + categoryId, '');

            // Reset values on server side via AJAX
            $.ajax({
                url: resetvalue,
                data: {
                    category_id: attributeSetId
                },
                type: 'POST',
                dataType: 'json'
            })
            .done(function(response) {
                // Optional: Handle success response if needed
            })
            .fail(function(jqXHR, textStatus, errorThrown) {
                console.error('Error resetting values:', textStatus, errorThrown);
                // Optional: Handle error response
            })
            .always(function() {
                // Reload the page after processing
                window.location.reload();
            });
        });

        // On click Next Button
        $('#next-question').on('click', function(e) {
            var birthDate = new Date($('#date').val());
            var date =  $("input[type=date]");
            if (date.length > 0) {
                if (isNaN(birthDate.getTime())) {
                    alert('Please select proper date value');
                    $('#date').val(''); 
                    return false; 
                }
            }
            if (birthDate) {
                var today = new Date();
                var age = today.getFullYear() - birthDate.getFullYear();
                var monthDiff = today.getMonth() - birthDate.getMonth();

                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                    age--;
                }
            
                if (age < 18) {
                    alert('You are not eligible. You must be 18 years or older.');
                    $('#date').val(''); 
                    return false; 
                } else if (age > 80) {
                    alert('You are not eligible. You must be 80 years or younger.');
                    $('#date').val(''); 
                    return false; 
                }
            }
            e.preventDefault();
            var isValid = true;
            var selectedOptionId = [];
            var isRadio = $("input[type=radio]");
            var allCheckboxes = $(".options input[type=checkbox]");

            var uncheckedCheckboxes = $(".options input[type=checkbox]:not(:checked)");
            var intial = $(".options input[type=checkbox]");
            let selectedGroupIds = [];

            // Check for checked checkboxes
            if (uncheckedCheckboxes.length !== 0 && uncheckedCheckboxes.length == intial.length) {
                alert('Please select proper checked value.');
                isValid = false;
            } else {
                if (uncheckedCheckboxes.length > 0) {

                    $("input[type=checkbox]:checked").each(function () {
                        selectedOptionId.push($(this).val());
                        var groupId = $(this).attr('data-group-id');

                        if (selectedGroupIds.indexOf(groupId) === -1) {
                            selectedGroupIds.push(groupId); // Add unique group IDs only
                        }

                    });
                    var selectedGroupId = selectedGroupIds.join(',');
                }
            }
            var selectedRadioValue = $("input[type=radio]:checked").val();
            var selectedRadioButton = $("input[type=radio]:checked");
            if (selectedRadioValue === undefined && isRadio.length > 0) {
                alert('Please select proper value.');
                isValid = false;
            } else {
                if (isRadio.length > 0) {
                    selectedOptionId = selectedRadioValue;
                    var selectedGroupId = selectedRadioButton.attr('data-group-id');
                }
            }
            var textValue = $("input[name=text_field]").val();
            var textfield = $("input[name=text_field]");
            if (textValue !== undefined && textValue.trim() !== '') {
                var selectedOptionId = textValue;
                var selectedGroupId = textfield.attr('data-group-id') || null;
            } else {
                if (textValue === '' && textfield.length > 0) {
                    alert('Please add proper value.');
                    isValid = false
                };
            }
            // File Input Validation
            var option = $("input[type=file]").val();
            if (option === '' && $("input[type=file]").length > 0) {
                alert('Please select proper file value.');
                isValid = false;
            } else if (option !== undefined) {
                selectedOptionId = option;
            }
            var date = $("input[name=date]");

            if (date.length > 0) {
                var selectedGroupId = date.attr('data-group-id') || null;
            }

            $("input[type=date]").on('change', function() {
                var option = $(this).val();
                if (option === '' && $("input[type=date]").length > 0) {
                    alert('Please select proper date value.');
                    isValid = false;
                } else if (option !== undefined) {
                    selectedOptionId = option;
                }
            });

            if (!isValid) {
                return;
            }

            var currentQuestionId = localStorage.getItem('nextQuestionId-' + categoryId);

            var attributeSetId = categoryAttributeValue;

            let questionSets = localStorage.getItem('question_back-' + categoryId);

            if (questionSets) {
                try {
                    questionSets = JSON.parse(questionSets);
                } catch (e) {
                    console.error("Error parsing JSON from localStorage", e);
                    questionSets = [];
                }
            } else {
                questionSets = [];
            }

            if (questionSets !== null) {
                let lastUsedId = questionSets.length > 0 ? questionSets[questionSets.length - 1].id : 0;
                const newId = lastUsedId + 1;

                questionSets.push({
                    id: newId,
                    currentQuestionId: currentQuestionId,
                    selectedOptionId: selectedOptionId,
                    attributeSetId: attributeSetId,
                    groupId: selectedGroupId
                });

                localStorage.setItem('question_sets-' + categoryId, JSON.stringify(questionSets));
                localStorage.setItem('question_back-' + categoryId, JSON.stringify(questionSets));
            }

            ajaxCallForGetQuestion(currentQuestionId, selectedOptionId, attributeSetId, selectedGroupId)

        });

        // On click Next Button this funcation call
        function ajaxCallForGetQuestion(currentQuestionId, selectedOptionId, attributeSetId, groupId) {
            localStorage.setItem('groupIds' + categoryId, groupId);

            localStorage.setItem('question_id_' + categoryId, currentQuestionId);
            localStorage.setItem('question_value_' + categoryId, selectedOptionId);

            $.ajax({
                url: getnextquestion,
                data: {
                    current_question_id: currentQuestionId,
                    selected_option_id: selectedOptionId,
                    attribute_set_id: attributeSetId,
                    group_id : groupId
                },
                type: 'POST',
                dataType: 'json',
                success: function(response) {

                    if (response && response.success == false) {
                        $('.customer_button button').hide();
                        $('.question p').text('Thank you for reaching out and providing the necessary information.  For further assistance or to explore other options, please feel free to contact us.');
                        $('.options').empty();
                        localStorage.setItem('question_id_' + categoryId, '');
                        localStorage.setItem('question_value_' + categoryId, '');
                        setTimeout(function () {
                            window.location.href = '/contact'; // Redirect to Contact Us page
                        }, 7000);
                    } else if (response.data && response.data.final) {
                        var url = response.data.productUrl;
                        window.location.href = url;

                        localStorage.setItem('question_id_' + categoryId, '');
                        localStorage.setItem('question_value_' + categoryId, '');
                        localStorage.setItem('question_sets-' + categoryId, '');
                        localStorage.setItem('question_back-' + categoryId, '');


                    } else if (response.data && response.data.question_id == null || response.data.question_id == '') {
                        $('.customer_button button').hide();

                        $('.question p').text('Thank you for reaching out and providing the necessary information. For further assistance or to explore other options, please feel free to contact us.');
                        $('.options').empty();
                        localStorage.setItem('question_id_' + categoryId, '');
                        localStorage.setItem('question_value_' + categoryId, '');
                        setTimeout(function () {
                            window.location.href = '/contact'; 
                        }, 7000);
                    } else {

                        if (response.data && response.data.question_id) {
                            localStorage.setItem('nextQuestionId-' + categoryId, response.data.question_id);
                            $('.question p').text(response.data.question);
                            $('.question').attr('data-question-id', response.data.question_id);

                            $('.options').empty();
                            if (response.data.type === 'text') {
                                var selectedValue = response.data.selected_value;
                                var groupids = localStorage.getItem('groupIds' + categoryId);

                                $('.options').append(
                                    '<div class="option">' +
                                    '<input required type="text" id="text_field" name="text_field" value="' + selectedValue + '" data-group-id="' + (groupids ? groupids : 'null') + '">' +
                                    '</div>'
                                );
                            }
                            else if (response.data.type === 'date') {
                                var selectedValue = response.data.selected_value;
                                var groupids = localStorage.getItem('groupIds' + categoryId);

                                $('.options').append(
                                    '<div class="option">' +
                                    '<input required type="date" id="date" name="date" value="' + selectedValue + '" data-group-id="' + (groupids ? groupids : 'null') + '">' +
                                    '</div>'
                                );
                            } else if (response.data.type === 'multiselect') {
                                var selectedValue = response.data.selected_value;
                                $.each(response.data.options, function(index, option) {
                                    var isSelected = selectedValue.includes(option.value.toString());
                                    var optionClass = isSelected ? 'selected' : '';

                                    $('.options').append(
                                        '<div class="option ' + optionClass + '" data-option-id="' + option.value + '">' +
                                        '<input required type="checkbox" name="option[]" id="option_' + option.value + '" value="' + option.value + '"' +
                                        (isSelected ? 'checked' : '') + ' data-group-id="' + option.groupid + '">' +
                                        '<label for="option_' + option.value + '">' + option.label + '</label>' +
                                        '</div>'
                                    );
                                });
                            } else if (response.data.type === 'media_image') {
                                $('.options').append(
                                    '<div class="option">' +
                                    '<input required type="file" name="option" class="choose_file" id="fileInput" accept="image/*">' + // Input field for uploading image files
                                    '</div>'
                                );

                            } else {

                                var selectedValue = response.data.selected_value;

                                $.each(response.data.options, function(index, option) {

                                    var isSelected = option.value.toString() === selectedValue.toString();

                                    var optionClass = isSelected ? 'selected' : '';

                                    if (option.images) {
                                        $('.options').append(
                                            '<div class="option ' + optionClass + '" data-option-id="' + option.value + '">' +
                                            '<img src="' + option.images + '" alt="Option Image">' +
                                            '<input required type="radio" name="option" id="option_' + option.value + '" value="' + option.value + '"' +
                                            (isSelected ? 'checked' : '') + ' data-group-id="' + option.groupid + '">' +
                                            '<label for="option_' + option.value + '">' + option.label + '</label>' +
                                            '</div>'
                                        );
                                    } else {
                                        $('.options').append(
                                            '<div class="option ' + optionClass + '" data-option-id="' + option.value + '">' +
                                            '<input required type="radio" name="option" id="option_' + option.value + '" value="' + option.value + '"' +
                                            (isSelected ? 'checked' : '') + ' data-group-id="' + option.groupid + '">' +
                                            '<label for="option_' + option.value + '">' + option.label + '</label>' +
                                            '</div>'
                                        );
                                    }

                                });
                            }
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.log(error);
                }
            });
        }
        var currentIndex = 1;

        // On click Back Button 
        $('#back-question').click(function() {
            localStorage.setItem('question_backword' + categoryId, true);

            var questionSets = JSON.parse(localStorage.getItem('question_back-' + categoryId));
            if (questionSets && questionSets.length > 0) {
                currentIndex--;

                if (currentIndex < 1) {
                    currentIndex = questionSets.length - 1;
                }

                var previousQuestionSet = questionSets[currentIndex];
                var currentQuestionId = previousQuestionSet.currentQuestionId;
                var selectedOptionId = previousQuestionSet.selectedOptionId;
                var attributeSetId = previousQuestionSet.attributeSetId;

                localStorage.setItem('question_value_' + categoryId, selectedOptionId);

                localStorage.setItem('question_id_' + categoryId, currentQuestionId);
                ajaxCallForGetBackQuestion(currentQuestionId, selectedOptionId, attributeSetId);

            } else {
                console.log("No question sets found.");
            }
        });

        // On click Back Button this funcation call 
        function ajaxCallForGetBackQuestion(currentQuestionId, selectedOptionId, attributeSetId) {
            var groupids = localStorage.getItem('groupIds' + categoryId);

            $.ajax({
                url: getbackquestion,
                data: {
                    current_question_id: currentQuestionId,
                    selected_option_id: selectedOptionId,
                    attribute_set_id: attributeSetId,
                    group_id: groupids
                },
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    var question_backword = localStorage.getItem('question_backword' + categoryId);

                    if (question_backword === "true") {
                        var questionSets = JSON.parse(localStorage.getItem('question_back-' + categoryId));

                        questionSets.splice(currentIndex, 1);
                        localStorage.setItem('question_back-' + categoryId, JSON.stringify(questionSets));
                    }
                    if (response && response.success == false) {

                        $('.question p').text('Thank you for reaching out and providing the necessary information. For further assistance or to explore other options, please feel free to contact us.');
                        $('.options').empty();
                        localStorage.setItem('question_id_' + categoryId, '');
                        localStorage.setItem('question_value_' + categoryId, '');

                    } else if (response.data && response.data.final) {
                        $('.question p').text('Product Name: ' + response.data.productName);
                        $('.question ').append(
                            '<img src="' + response.data.productImage + '" alt="Product Image">'
                        );
                        $('.options').empty();
                        localStorage.setItem('question_id_' + categoryId, '');
                        localStorage.setItem('question_value_' + categoryId, '');

                    } else if (response.data && response.data.question_id == null || response.data.question_id == '') {
                        $('.question p').text('Thank you for reaching out and providing the necessary information. For further assistance or to explore other options, please feel free to contact us.');
                        $('.options').empty();
                        localStorage.setItem('question_id_' + categoryId, '');
                        localStorage.setItem('question_value_' + categoryId, '');

                    } else {
                        if (response.data && response.data.question_id) {
                            localStorage.setItem('nextQuestionId-' + categoryId, response.data.question_id);
                            $('.question p').text(response.data.question);
                            $('.question').attr('data-question-id', response.data.question_id);

                            $('.options').empty();
                            if (response.data.type === 'text') {
                                var fieldValue = selectedOptionId ? selectedOptionId : '';
                                var groupids = localStorage.getItem('groupIds' + categoryId);

                                $('.options').append(
                                    '<div class="option">' +
                                    '<input required type="text" id="text_field" name="text_field" value="' + fieldValue + '" data-group-id="' + (groupids ? groupids : 'null') + '>' +
                                    '</div>'
                                );
                            }
                            else if (response.data.type === 'date') {
                                var fieldValue = selectedOptionId ? selectedOptionId : '';
                                var groupids = localStorage.getItem('groupIds' + categoryId);

                                $('.options').append(
                                    '<div class="option">' +
                                    '<input required type="date" id="date" name="date" value="' + fieldValue + '" data-group-id="' + (groupids ? groupids : 'null') + '">' +
                                    '</div>'
                                );
                            } else if (response.data.type === 'multiselect') {
                                $.each(response.data.options, function(index, option) {
                                    var isSelected = selectedOptionId.includes(option.value);
                                    var optionClass = isSelected ? 'selected' : '';

                                    $('.options').append(
                                        '<div class="option ' + optionClass + '" data-option-id="' + option.value + '">' +
                                        '<input required type="checkbox" name="option[]" id="option_' + option.value + '" value="' + option.value + '"' +
                                        (isSelected ? 'checked' : '') + ' data-group-id="' + option.groupid + '">' +
                                        '<label for="option_' + option.value + '">' + option.label + '</label>' +
                                        '</div>'
                                    );
                                });
                            } else if (response.data.type === 'media_image') {
                                $('.options').append(
                                    '<div class="option">' +
                                    '<input required type="file" name="option" class="choose_file" id="fileInput" accept="image/*">' + // Input field for uploading image files
                                    '</div>'
                                );

                            } else {
                                $.each(response.data.options, function(index, option) {
                                    var isSelected = option.value === selectedOptionId;
                                    var optionClass = isSelected ? 'selected' : '';

                                    if (option.images) {
                                        $('.options').append(
                                            '<div class="option ' + optionClass + '" data-option-id="' + option.value + '">' +
                                            '<img src="' + option.images + '" alt="Option Image">' +
                                            '<input required type="radio" name="option" id="option_' + option.value + '" value="' + option.value + '"' +
                                            (isSelected ? 'checked' : '') + ' data-group-id="' + option.groupid + '">' +
                                            '<label for="option_' + option.value + '">' + option.label + '</label>' +
                                            '</div>'
                                        );
                                    } else {
                                        $('.options').append(
                                            '<div class="option ' + optionClass + '" data-option-id="' + option.value + '">' +
                                            '<input required type="radio" name="option" id="option_' + option.value + '" value="' + option.value + '"' +
                                            (isSelected ? 'checked' : '') + ' data-group-id="' + option.groupid + '">' +
                                            '<label for="option_' + option.value + '">' + option.label + '</label>' +
                                            '</div>'
                                        );
                                    }

                                });
                            }
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.log(error);
                }
            });
        }
    });
});