/**
 * @brief 카운터 정보 수집 javascript
 * window.onload 이벤트 후에 counter 모듈을 호출한다.
 **/

// 이벤트 등록
xAddEventListener(window,'load',doCallCounter);

// counter 모듈을 호출하는 함수
function doCallCounter() {
    exec_xml('counter','procCounterExecute');
}
