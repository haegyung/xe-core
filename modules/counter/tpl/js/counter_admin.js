// 관리자 페이지에서 날자 이동
function changeSelectedDate(selected_date) {
    var fo_obj = xGetElementById('fo_counter');
    fo_obj.selected_date.value = selected_date;
    fo_obj.submit();
}
