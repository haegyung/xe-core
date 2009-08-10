<?php
    /**
     * @file   modules/install/lang/ko.lang.php
     * @author zero (zero@nzeo.com)
     * @brief  한국어 언어팩 (기본적인 내용만 수록)
     **/

    $lang->introduce_title = 'XE 설치';
    $lang->license = <<<EndOfLicense
이 프로그램은 자유 소프트웨어 이며 GPL을 따릅니다.
단 디자인 요소가 첨부된 스킨의 경우는 해당 스킨 제작자가 개별적인 라이선스를 적용할 수 있습니다.
번역문과 원문의 내용상 차이가 발생시 원문의 내용을 따르게 됩니다.

<b>GNU 일반 공중 사용 허가서</b> -  번역문
2판, 1991년 6월
Copyright (C) 1989, 1991 Free Software Foundation, Inc.  59 Temple Place - Suite 330, Boston, MA  02111-1307, USA
누구든지 본 사용 허가서를 있는 그대로 복제하고 배포할 수 있습니다. 그러나 본문에 대한 수정은 허용되지 않습니다. 

전 문

소프트웨어에 적용되는 대부분의 사용 허가서(license)들은 소프트웨어에 대한 수정과 공유의 자유를 제한하려는 것을 그 목적으로 합니다. 그러나 GNU 일반 공중 사용 허가서(이하, ``GPL''이라고 칭합니다.)는 자유 소프트웨어에 대한 수정과 공유의 자유를 모든 사용자들에게 보장하기 위해서 성립된 것입니다. 자유 소프트웨어 재단이 제공하는 대부분의 소프트웨어들은 GPL에 의해서 관리되고 있으며, 몇몇 소프트웨어에는 별도의 사용 허가서인 GNU 라이브러리 일반 공중 사용 허가서(GNU Library General Public License)를 대신 적용하기도 합니다. 자유 소프트웨어란, 이를 사용하려고 하는 모든 사람에 대해서 동일한 자유와 권리가 함께 양도되는 소프트웨어를 말하며 프로그램 저작자의 의지에 따라 어떠한 종류의 프로그램에도 GPL을 적용할 수 있습니다. 따라서 여러분이 만든 프로그램에도 GPL을 적용할 수 있습니다.

자유 소프트웨어를 언급할 때 사용되는 ``자유''라는 단어는 무료(無料)를 의미하는 금전적인 측면의 자유가 아니라 구속되지 않는다는 관점에서의 자유를 의미하며, GPL은 자유 소프트웨어를 이용한 복제와 개작, 배포와 수익 사업 등의 가능한 모든 형태의 자유를 실질적으로 보장하고 있습니다. 여기에는 원시 코드(source code)의 전부 또는 일부를 원용해서 개선된 프로그램을 만들거나 새로운 프로그램을 창작할 수 있는 자유가 포함되며, 자신에게 양도된 이러한 자유와 권리를 보다 명확하게 인식할 수 있도록 하기 위한 규정도 포함되어 있습니다.

GPL은 GPL 안에 소프트웨어를 양도받을 사용자의 권리를 제한하는 조항과 단서를 별항으로 추가시키지 못하게 함으로써 사용자들의 자유와 권리를 실제적으로 보장하고 있습니다. 자유 소프트웨어의 개작과 배포에 관계하고 있는 사람들은 이러한 무조건적인 권리 양도 규정을 준수해야만 합니다.

예를 들어 GPL 프로그램을 배포할 경우에는 프로그램의 유료 판매나 무료 배포에 관계없이 자신이 해당 프로그램에 대해서 가질 수 있었던 모든 권리를, 프로그램을 받게될 사람에게 그대로 양도해 주어야 합니다. 이 경우, 프로그램의 원시 코드를 함께 제공하거나 원시 코드를 구할 수 있는 방법을 확실히 알려주어야 하고 이러한 모든 사항들을 사용자들이 분명히 알 수 있도록 명시해야 합니다.

자유 소프트웨어 재단은 다음과 같은 두 가지 단계를 통해서 사용자들을 권리를 보호합니다. (1) 소프트웨어에 저작권을 설정합니다. (2) 저작권의 양도에 관한 실정법에 의해서 유효한 법률적 효력을 갖는 GPL을 통해 소프트웨어를 복제하거나 개작 및 배포할 수 있는 권리를 사용자들에게 부여합니다.

자유 소프트웨어를 사용하는 사람들은 반복적인 재배포 과정을 통해 소프트웨어 자체에 수정과 변형이 일어날 수도 있으며, 이는 최초의 저작자가 만든 소프트웨어가 갖고 있는 문제가 아닐 수 있다는 개연성을 인식하고 있어야 합니다. 우리는 개작과 재배포 과정에서 다른 사람에 의해 발생된 문제로 인해 프로그램 원저작자들의 신망이 훼손되는 것을 원하지 않습니다. GPL에 자유 소프트웨어에 대한 어떠한 형태의 보증도 규정하지 않는 이유는 이러한 점들이 고려되었기 때문이며, 이는 프로그램 원저작자와 자유 소프트웨어 재단의 자유로운 활동을 보장하는 현실적인 수단이기도 합니다.

특허 제도는 자유 소프트웨어의 발전을 위협하는 요소일 수밖에 없습니다. 자유 프로그램을 재배포하는 사람들이 개별적으로 특허를 취득하게 되면, 결과적으로 그 프로그램이 독점 소프트웨어가 될 가능성이 있습니다. 자유 소프트웨어 재단은 이러한 문제에 대처하기 위해서 어떠한 특허에 대해서도 그 사용 권리를 모든 사람들(이하, ``공중(公衆)''이라고 칭합니다.)에게 자유롭게 허용하는 경우에 한해서만 자유 소프트웨어와 함께 사용할 수 있다는 것을 명확히 밝히고 있습니다.

복제(copying)와 개작(modification) 및 배포(distribution)에 관련된 구체적인 조건과 규정은 다음과 같습니다.

복제와 개작 및 배포에 관한 조건과 규정

제 0 조. 본 허가서는 GNU 일반 공중 사용 허가서의 규정에 따라 배포될 수 있다는 사항이 저작권자에 의해서 명시된 모든 컴퓨터 프로그램 저작물에 대해서 동일하게 적용됩니다. 컴퓨터 프로그램 저작물(이하, ``프로그램''이라고 칭합니다.)이란 특정한 결과를 얻기 위해서 컴퓨터 등의 정보 처리 능력을 가진 장치(이하, ``컴퓨터''라고 칭합니다.) 내에서 직접 또는 간접으로 사용되는 일련의 지시 및 명령으로 표현된 창작물을 의미하고, ``2차적 프로그램''이란 전술한 프로그램 자신 또는 저작권법의 규정에 따라 프로그램의 전부 또는 상당 부분을 원용하거나 다른 언어로의 번역을 포함할 수 있는 개작 과정을 통해서 창작된 새로운 프로그램과 이와 관련된 저작물을 의미합니다. (이후로 다른 언어로의 번역은 별다른 제한없이 개작의 범위에 포함되는 것으로 간주합니다.) ``피양도자''란 GPL의 규정에 따라 프로그램을 양도받은 사람을 의미하고, ``원(原)프로그램''이란 프로그램을 개작하거나 2차적 프로그램을 만들기 위해서 사용된 최초의 프로그램을 의미합니다.

본 허가서는 프로그램에 대한 복제와 개작 그리고 배포 행위에 대해서만 적용됩니다. 따라서 프로그램을 실행시키는 행위에 대한 제한은 없습니다. 프로그램의 결과물(output)에는, 그것이 프로그램을 실행시켜서 생성된 것인지 아닌지의 여부에 상관없이 결과물의 내용이 원프로그램으로부터 파생된 2차적 프로그램을 구성했을 때에 한해서 본 허가서의 규정들이 적용됩니다. 2차적 프로그램의 구성 여부는 2차적 프로그램 안에서의 원프로그램의 역할을 토대로 판단합니다.

제 1 조. 적절한 저작권 표시와 프로그램에 대한 보증이 제공되지 않는다는 사실을 각각의 복제물에 명시하는 한, 피양도자는 프로그램의 원시 코드를 자신이 양도받은 상태 그대로 어떠한 매체를 통해서도 복제하고 배포할 수 있습니다. 복제와 배포가 이루어 질 때는 본 허가서와 프로그램에 대한 보증이 제공되지 않는다는 사실에 대해서 언급되었던 모든 내용을 그대로 유지시켜야 하며, 영문판 GPL을 함께 제공해야 합니다.

배포자는 복제물을 물리적으로 인도하는데 소요된 비용을 청구할 수 있으며, 선택 사항으로 독자적인 유료 보증을 설정할 수 있습니다.

제 2 조. 피양도자는 자신이 양도받은 프로그램의 전부나 일부를 개작할 수 있으며, 이를 통해서 2차적 프로그램을 창작할 수 있습니다. 개작된 프로그램이나 창작된 2차적 프로그램은 다음의 사항들을 모두 만족시키는 조건에 한해서, 제1조의 규정에 따라 또다시 복제되고 배포될 수 있습니다.

제 1 항. 파일을 개작할 때는 파일을 개작한 사실과 그 날짜를 파일 안에 명시해야 합니다.

제 2 항. 배포하거나 공표하려는 저작물의 전부 또는 일부가 양도받은 프로그램으로부터 파생된 것이라면, 저작물 전체에 대한 사용 권리를 본 허가서의 규정에 따라 공중에게 무상으로 허용해야 합니다.

제 3 항. 개작된 프로그램의 일반적인 실행 형태가 대화형 구조로 명령어를 읽어 들이는 방식을 취하고 있을 경우에는, 적절한 저작권 표시와 프로그램에 대한 보증이 제공되지 않는다는 사실, (별도의 보증을 설정한 경우라면 해당 내용) 그리고 양도받은 프로그램을 본 규정에 따라 재배포할 수 있다는 사실과 GPL 사본을 참고할 수 있는 방법이 함께 포함된 문구가 프로그램이 대화형 구조로 평이하게 실행된 직후에 화면 또는 지면으로 출력되도록 작성되어야 합니다. (예외 규정: 양도받은 프로그램이 대화형 구조를 갖추고 있다 하더라도 통상적인 실행 환경에서 전술한 사항들이 출력되지 않는 형태였을 경우에는 이를 개작한 프로그램 또한 관련 사항들을 출력시키지 않아도 무방합니다.) 

위의 조항들은 개작된 프로그램 전체에 적용됩니다. 만약, 개작된 프로그램에 포함된 특정 부분이 원프로그램으로부터 파생된 것이 아닌 별도의 독립 저작물로 인정될 만한 상당한 이유가 있을 경우에는 해당 저작물의 개별적인 배포에는 본 허가서의 규정들이 적용되지 않습니다. 그러나 이러한 저작물이 2차적 프로그램의 일부로서 함께 배포된다면 개별적인 저작권과 배포 기준에 상관없이 저작물 모두에 본 허가서가 적용되어야 하며, 전체 저작물에 대한 사용 권리는 공중에게 무상으로 양도됩니다.

이러한 규정은 개별적인 저작물에 대한 저작자의 권리를 침해하거나 인정하지 않으려는 것이 아니라, 원프로그램으로부터 파생된 2차적 프로그램이나 수집 저작물의 배포를 일관적으로 규제할 수 있는 권리를 행사하기 위한 것입니다.

원프로그램이나 원프로그램으로부터 파생된 2차적 프로그램을 이들로부터 파생되지 않은 다른 저작물과 함께 단순히 저장하거나 배포할 목적으로 동일한 매체에 모아 놓은 집합물의 경우에는, 원프로그램으로부터 파생되지 않은 다른 저작물에는 본 허가서의 규정들이 적용되지 않습니다.

제 3 조. 피양도자는 다음 중 하나의 항목을 만족시키는 조건에 한해서 제1조와 제2조의 규정에 따라 프로그램(또는 제2조에서 언급된 2차적 프로그램)을 목적 코드(object code)나 실행물(executable form)의 형태로 복제하고 배포할 수 있습니다.

제 1 항. 목적 코드나 실행물에 상응하는 컴퓨터가 인식할 수 있는 완전한 원시 코드를 함께 제공해야 합니다. 원시 코드는 제1조와 제2조의 규정에 따라 배포될 수 있어야 하며, 소프트웨어의 교환을 위해서 일반적으로 사용되는 매체를 통해 제공되어야 합니다.

제 2 항. 배포에 필요한 최소한의 비용만을 받고 목적 코드나 실행물에 상응하는 완전한 원시 코드를 배포하겠다는, 최소한 3년간 유효한 약정서를 함께 제공해야 합니다. 이 약정서는 약정서를 갖고 있는 어떠한 사람에 대해서도 유효해야 합니다. 원시 코드는 컴퓨터가 인식할 수 있는 형태여야 하고 제1조와 제2조의 규정에 따라 배포될 수 있어야 하며, 소프트웨어의 교환을 위해서 일반적으로 사용되는 매체를 통해 제공되어야 합니다.

제 3 항. 목적 코드나 실행물에 상응하는 원시 코드를 배포하겠다는 약정에 대해서 자신이 양도받은 정보를 함께 제공해야 합니다. (제3항은 위의 제2항에 따라 원시 코드를 배포하겠다는 약정을 프로그램의 목적 코드나 실행물과 함께 제공 받았고, 동시에 비상업적인 배포를 하고자 할 경우에 한해서만 허용됩니다.) 

저작물에 대한 원시 코드란 해당 저작물을 개작하기에 적절한 형식을 의미합니다. 실행물에 대한 완전한 원시 코드란 실행물에 포함된 모든 모듈들의 원시 코드와 이와 관련된 인터페이스 정의 파일 모두, 그리고 실행물의 컴파일과 설치를 제어하는데 사용된 스크립트 전부를 의미합니다. 그러나 특별한 예외의 하나로서, 실행물이 실행될 운영체제의 주요 부분(컴파일러나 커널 등)과 함께 (원시 코드나 바이너리의 형태로) 일반적으로 배포되는 구성 요소들은 이러한 구성 요소 자체가 실행물에 수반되지 않는 한 원시 코드의 배포 대상에서 제외되어도 무방합니다.

목적 코드나 실행물을 지정한 장소로부터 복제해 갈 수 있게 하는 방식으로 배포할 경우, 동일한 장소로부터 원시 코드를 복제할 수 있는 동등한 접근 방법을 제공한다면 이는 원시 코드를 목적 코드와 함께 복제되도록 설정하지 않았다고 하더라도 원시 코드를 배포하는 것으로 간주됩니다.

제 4 조. 본 허가서에 의해 명시적으로 이루어 지지 않는 한 프로그램에 대한 복제와 개작 및 하위 허가권 설정과 배포가 성립될 수 없습니다. 이와 관련된 어떠한 행위도 무효이며 본 허가서가 보장한 권리는 자동으로 소멸됩니다. 그러나 본 허가서의 규정에 따라 프로그램의 복제물이나 권리를 양도받았던 제3자는 본 허가서의 규정들을 준수하는 한, 배포자의 권리 소멸에 관계없이 사용상의 권리를 계속해서 유지할 수 있습니다.

제 5 조. 본 허가서는 서명이나 날인이 수반되는 형식을 갖고 있지 않기 때문에 피양도자가 본 허가서의 내용을 반드시 받아들여야 할 필요는 없습니다. 그러나 프로그램이나 프로그램에 기반한 2차적 프로그램에 대한 개작 및 배포를 허용하는 것은 본 허가서에 의해서만 가능합니다. 만약 본 허가서에 동의하지 않을 경우에는 이러한 행위들이 법률적으로 금지됩니다. 따라서 프로그램(또는 프로그램에 기반한 2차적 프로그램)을 개작하거나 배포하는 행위는 이에 따른 본 허가서의 내용에 동의한다는 것을 의미하며, 복제와 개작 및 배포에 관한 본 허가서의 조건과 규정들을 모두 받아들이겠다는 의미로 간주됩니다.

제 6 조. 피양도자에 의해서 프로그램(또는 프로그램에 기반한 2차적 프로그램)이 반복적으로 재배포될 경우, 각 단계에서의 피양도자는 본 허가서의 규정에 따른 프로그램의 복제와 개작 및 배포에 대한 권리를 최초의 양도자로부터 양도받은 것으로 자동적으로 간주됩니다. 프로그램(또는 프로그램에 기반한 2차적 프로그램)을 배포할 때는 피양도자의 권리의 행사를 제한할 수 있는 어떠한 사항도 추가할 수 없습니다. 그러나 피양도자에게, 재배포가 일어날 시점에서의 제3의 피양도자에게 본 허가서를 준수하도록 강제할 책임은 부과되지 않습니다.

제 7 조. 법원의 판결이나 특허권 침해에 대한 주장 또는 특허 문제에 국한되지 않은 그밖의 이유들로 인해서 본 허가서의 규정에 배치되는 사항이 발생한다 하더라도 그러한 사항이 선행하거나 본 허가서의 조건과 규정들이 면제되는 것은 아닙니다. 따라서 법원의 명령이나 합의 등에 의해서 본 허가서에 위배되는 사항들이 발생한 상황이라도 양측 모두를 만족시킬 수 없다면 프로그램은 배포될 수 없습니다. 예를 들면, 특정한 특허 관련 허가가 프로그램의 복제물을 직접 또는 간접적인 방법으로 양도받은 임의의 제3자에게 해당 프로그램을 무상으로 재배포할 수 있게 허용하지 않는다면, 그러한 허가와 본 사용 허가를 동시에 만족시키면서 프로그램을 배포할 수 있는 방법은 없습니다.

본 조항은 특정한 상황에서 본 조항의 일부가 유효하지 않거나 적용될 수 없을 경우에도 본 조항의 나머지 부분들을 적용하기 위한 의도로 만들어 졌습니다. 따라서 그 이외의 상황에서는 본 조항을 전체적으로 적용하면 됩니다.

본 조항의 목적은 특허나 저작권 침해 등의 행위를 조장하거나 해당 권리를 인정하지 않으려는 것이 아니라, GPL을 통해서 구현되어 있는 자유 소프트웨어의 배포 체계를 통합적으로 보호하기 위한 것입니다. 많은 사람들이 배포 체계에 대한 신뢰있는 지원을 계속해 줌으로써 소프트웨어의 다양한 분야에 많은 공헌을 해 주었습니다. 소프트웨어를 어떠한 배포 체계로 배포할 것인가를 결정하는 것은 전적으로 저작자와 기증자들의 의지에 달려있는 것이지, 일반 사용자들이 강요할 수 있는 문제는 아닙니다.

본 조항은 본 허가서의 다른 조항들에서 무엇이 중요하게 고려되어야 하는 지를 명확하게 설명하기 위한 목적으로 만들어진 것입니다.

제 8 조. 특허나 저작권이 설정된 인터페이스로 인해서 특정 국가에서 프로그램의 배포와 사용이 함께 또는 개별적으로 제한되어 있는 경우, 본 사용 허가서를 프로그램에 적용한 최초의 저작권자는 문제가 발생하지 않는 국가에 한해서 프로그램을 배포한다는 배포상의 지역적 제한 조건을 명시적으로 설정할 수 있으며, 이러한 사항은 본 허가서의 일부로 간주됩니다.

제 9 조. 자유 소프트웨어 재단은 때때로 본 사용 허가서의 개정판이나 신판을 공표할 수 있습니다. 새롭게 공표될 판은 당면한 문제나 현안을 처리하기 위해서 세부적인 내용에 차이가 발생할 수 있지만, 그 근본 정신에는 변함이 없을 것입니다.

각각의 판들은 판번호를 사용해서 구별됩니다. 특정한 판번호와 그 이후 판을 따른다는 사항이 명시된 프로그램에는 해당 판이나 그 이후에 발행된 어떠한 판을 선택해서 적용해도 무방하고, 판번호를 명시하고 있지 않은 경우에는 자유 소프트웨어 재단이 공표한 어떠한 판번호의 판을 적용해도 무방합니다.

제 10 조. 프로그램의 일부를 본 허가서와 배포 기준이 다른 자유 프로그램과 함께 결합하고자 할 경우에는 해당 프로그램의 저작자로부터 서면 승인을 받아야 합니다. 자유 소프트웨어 재단이 저작권을 갖고 있는 소프트웨어의 경우에는 자유 소프트웨어 재단의 승인을 얻어야 합니다. 우리는 이러한 요청을 수락하기 위해서 때때로 예외 기준을 만들기도 합니다. 자유 소프트웨어 재단은 일반적으로 자유 소프트웨어의 2차적 저작물들을 모두 자유로운 상태로 유지시키려는 목적과 소프트웨어의 공유와 재활용을 증진시키려는 두가지 목적을 기준으로 승인 여부를 결정할 것입니다.

보증의 결여 (제11조, 제12조)

제 11 조. 본 허가서를 따르는 프로그램은 무상으로 양도되기 때문에 관련 법률이 허용하는 한도 내에서 어떠한 형태의 보증도 제공되지 않습니다. 프로그램의 저작권자와 배포자가 공동 또는 개별적으로 별도의 보증을 서면으로 제공할 때를 제외하면, 특정한 목적에 대한 프로그램의 적합성이나 상업성 여부에 대한 보증을 포함한 어떠한 형태의 보증도 명시적이나 묵시적으로 설정되지 않은 ``있는 그대로의'' 상태로 이 프로그램을 배포합니다. 프로그램과 프로그램의 실행에 따라 발생할 수 있는 모든 위험은 피양도자에게 인수되며 이에 따른 보수 및 복구를 위한 제반 경비 또한 피양도자가 모두 부담해야 합니다.

제 12 조. 저작권자나 배포자가 프로그램의 손상 가능성을 사전에 알고 있었다 하더라도 발생된 손실이 관련 법규에 의해 보호되고 있거나 이에 대한 별도의 서면 보증이 설정된 경우가 아니라면, 저작권자나 프로그램을 원래의 상태 또는 개작한 상태로 제공한 배포자는 프로그램의 사용이나 비작동으로 인해 발생된 손실이나 프로그램 자체의 손실에 대해 책임지지 않습니다. 이러한 면책 조건은 사용자나 제3자가 프로그램을 조작함으로써 발생된 손실이나 다른 소프트웨어와 프로그램을 함께 동작시키는 것으로 인해서 발생된 데이터의 상실 및 부정확한 산출 결과에만 국한되는 것이 아닙니다. 발생된 손실의 일반성이나 특수성 뿐 아니라 원인의 우발성 및 필연성도 전혀 고려되지 않습니다.

복제와 개작 및 배포에 관한 조건과 규정의 끝.

<b>GNU 일반 공중 사용 허가서</b> - 원문

GNU GENERAL PUBLIC LICENSE

Version 2, June 1991

Copyright (C) 1989, 1991 Free Software Foundation, Inc.  51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA
Everyone is permitted to copy and distribute verbatim copies of this license document, but changing it is not allowed.

Preamble

The licenses for most software are designed to take away your freedom to share and change it. By contrast, the GNU General Public License is intended to guarantee your freedom to share and change free software--to make sure the software is free for all its users. This General Public License applies to most of the Free Software Foundation's software and to any other program whose authors commit to using it. (Some other Free Software Foundation software is covered by the GNU Lesser General Public License instead.) You can apply it to your programs, too.

When we speak of free software, we are referring to freedom, not price. Our General Public Licenses are designed to make sure that you have the freedom to distribute copies of free software (and charge for this service if you wish), that you receive source code or can get it if you want it, that you can change the software or use pieces of it in new free programs; and that you know you can do these things.

To protect your rights, we need to make restrictions that forbid anyone to deny you these rights or to ask you to surrender the rights. These restrictions translate to certain responsibilities for you if you distribute copies of the software, or if you modify it.

For example, if you distribute copies of such a program, whether gratis or for a fee, you must give the recipients all the rights that you have. You must make sure that they, too, receive or can get the source code. And you must show them these terms so they know their rights.

We protect your rights with two steps: (1) copyright the software, and (2) offer you this license which gives you legal permission to copy, distribute and/or modify the software.

Also, for each author's protection and ours, we want to make certain that everyone understands that there is no warranty for this free software. If the software is modified by someone else and passed on, we want its recipients to know that what they have is not the original, so that any problems introduced by others will not reflect on the original authors' reputations.

Finally, any free program is threatened constantly by software patents. We wish to avoid the danger that redistributors of a free program will individually obtain patent licenses, in effect making the program proprietary. To prevent this, we have made it clear that any patent must be licensed for everyone's free use or not licensed at all.

The precise terms and conditions for copying, distribution and modification follow.
TERMS AND CONDITIONS FOR COPYING, DISTRIBUTION AND MODIFICATION

0. This License applies to any program or other work which contains a notice placed by the copyright holder saying it may be distributed under the terms of this General Public License. The "Program", below, refers to any such program or work, and a "work based on the Program" means either the Program or any derivative work under copyright law: that is to say, a work containing the Program or a portion of it, either verbatim or with modifications and/or translated into another language. (Hereinafter, translation is included without limitation in the term "modification".) Each licensee is addressed as "you".

Activities other than copying, distribution and modification are not covered by this License; they are outside its scope. The act of running the Program is not restricted, and the output from the Program is covered only if its contents constitute a work based on the Program (independent of having been made by running the Program). Whether that is true depends on what the Program does.

1. You may copy and distribute verbatim copies of the Program's source code as you receive it, in any medium, provided that you conspicuously and appropriately publish on each copy an appropriate copyright notice and disclaimer of warranty; keep intact all the notices that refer to this License and to the absence of any warranty; and give any other recipients of the Program a copy of this License along with the Program.

You may charge a fee for the physical act of transferring a copy, and you may at your option offer warranty protection in exchange for a fee.

2. You may modify your copy or copies of the Program or any portion of it, thus forming a work based on the Program, and copy and distribute such modifications or work under the terms of Section 1 above, provided that you also meet all of these conditions:

a) You must cause the modified files to carry prominent notices stating that you changed the files and the date of any change. 
b) You must cause any work that you distribute or publish, that in whole or in part contains or is derived from the Program or any part thereof, to be licensed as a whole at no charge to all third parties under the terms of this License. 
c) If the modified program normally reads commands interactively when run, you must cause it, when started running for such interactive use in the most ordinary way, to print or display an announcement including an appropriate copyright notice and a notice that there is no warranty (or else, saying that you provide a warranty) and that users may redistribute the program under these conditions, and telling the user how to view a copy of this License. (Exception: if the Program itself is interactive but does not normally print such an announcement, your work based on the Program is not required to print an announcement.) 

These requirements apply to the modified work as a whole. If identifiable sections of that work are not derived from the Program, and can be reasonably considered independent and separate works in themselves, then this License, and its terms, do not apply to those sections when you distribute them as separate works. But when you distribute the same sections as part of a whole which is a work based on the Program, the distribution of the whole must be on the terms of this License, whose permissions for other licensees extend to the entire whole, and thus to each and every part regardless of who wrote it.

Thus, it is not the intent of this section to claim rights or contest your rights to work written entirely by you; rather, the intent is to exercise the right to control the distribution of derivative or collective works based on the Program.

In addition, mere aggregation of another work not based on the Program with the Program (or with a work based on the Program) on a volume of a storage or distribution medium does not bring the other work under the scope of this License.

3. You may copy and distribute the Program (or a work based on it, under Section 2) in object code or executable form under the terms of Sections 1 and 2 above provided that you also do one of the following:

a) Accompany it with the complete corresponding machine-readable source code, which must be distributed under the terms of Sections 1 and 2 above on a medium customarily used for software interchange; or, 
b) Accompany it with a written offer, valid for at least three years, to give any third party, for a charge no more than your cost of physically performing source distribution, a complete machine-readable copy of the corresponding source code, to be distributed under the terms of Sections 1 and 2 above on a medium customarily used for software interchange; or, 
c) Accompany it with the information you received as to the offer to distribute corresponding source code. (This alternative is allowed only for noncommercial distribution and only if you received the program in object code or executable form with such an offer, in accord with Subsection b above.) 

The source code for a work means the preferred form of the work for making modifications to it. For an executable work, complete source code means all the source code for all modules it contains, plus any associated interface definition files, plus the scripts used to control compilation and installation of the executable. However, as a special exception, the source code distributed need not include anything that is normally distributed (in either source or binary form) with the major components (compiler, kernel, and so on) of the operating system on which the executable runs, unless that component itself accompanies the executable.

If distribution of executable or object code is made by offering access to copy from a designated place, then offering equivalent access to copy the source code from the same place counts as distribution of the source code, even though third parties are not compelled to copy the source along with the object code.

4. You may not copy, modify, sublicense, or distribute the Program except as expressly provided under this License. Any attempt otherwise to copy, modify, sublicense or distribute the Program is void, and will automatically terminate your rights under this License. However, parties who have received copies, or rights, from you under this License will not have their licenses terminated so long as such parties remain in full compliance.

5. You are not required to accept this License, since you have not signed it. However, nothing else grants you permission to modify or distribute the Program or its derivative works. These actions are prohibited by law if you do not accept this License. Therefore, by modifying or distributing the Program (or any work based on the Program), you indicate your acceptance of this License to do so, and all its terms and conditions for copying, distributing or modifying the Program or works based on it.

6. Each time you redistribute the Program (or any work based on the Program), the recipient automatically receives a license from the original licensor to copy, distribute or modify the Program subject to these terms and conditions. You may not impose any further restrictions on the recipients' exercise of the rights granted herein. You are not responsible for enforcing compliance by third parties to this License.

7. If, as a consequence of a court judgment or allegation of patent infringement or for any other reason (not limited to patent issues), conditions are imposed on you (whether by court order, agreement or otherwise) that contradict the conditions of this License, they do not excuse you from the conditions of this License. If you cannot distribute so as to satisfy simultaneously your obligations under this License and any other pertinent obligations, then as a consequence you may not distribute the Program at all. For example, if a patent license would not permit royalty-free redistribution of the Program by all those who receive copies directly or indirectly through you, then the only way you could satisfy both it and this License would be to refrain entirely from distribution of the Program.

If any portion of this section is held invalid or unenforceable under any particular circumstance, the balance of the section is intended to apply and the section as a whole is intended to apply in other circumstances.

It is not the purpose of this section to induce you to infringe any patents or other property right claims or to contest validity of any such claims; this section has the sole purpose of protecting the integrity of the free software distribution system, which is implemented by public license practices. Many people have made generous contributions to the wide range of software distributed through that system in reliance on consistent application of that system; it is up to the author/donor to decide if he or she is willing to distribute software through any other system and a licensee cannot impose that choice.

This section is intended to make thoroughly clear what is believed to be a consequence of the rest of this License.

8. If the distribution and/or use of the Program is restricted in certain countries either by patents or by copyrighted interfaces, the original copyright holder who places the Program under this License may add an explicit geographical distribution limitation excluding those countries, so that distribution is permitted only in or among countries not thus excluded. In such case, this License incorporates the limitation as if written in the body of this License.

9. The Free Software Foundation may publish revised and/or new versions of the General Public License from time to time. Such new versions will be similar in spirit to the present version, but may differ in detail to address new problems or concerns.

Each version is given a distinguishing version number. If the Program specifies a version number of this License which applies to it and "any later version", you have the option of following the terms and conditions either of that version or of any later version published by the Free Software Foundation. If the Program does not specify a version number of this License, you may choose any version ever published by the Free Software Foundation.

10. If you wish to incorporate parts of the Program into other free programs whose distribution conditions are different, write to the author to ask for permission. For software which is copyrighted by the Free Software Foundation, write to the Free Software Foundation; we sometimes make exceptions for this. Our decision will be guided by the two goals of preserving the free status of all derivatives of our free software and of promoting the sharing and reuse of software generally.

NO WARRANTY

11. BECAUSE THE PROGRAM IS LICENSED FREE OF CHARGE, THERE IS NO WARRANTY FOR THE PROGRAM, TO THE EXTENT PERMITTED BY APPLICABLE LAW. EXCEPT WHEN OTHERWISE STATED IN WRITING THE COPYRIGHT HOLDERS AND/OR OTHER PARTIES PROVIDE THE PROGRAM "AS IS" WITHOUT WARRANTY OF ANY KIND, EITHER EXPRESSED OR IMPLIED, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE. THE ENTIRE RISK AS TO THE QUALITY AND PERFORMANCE OF THE PROGRAM IS WITH YOU. SHOULD THE PROGRAM PROVE DEFECTIVE, YOU ASSUME THE COST OF ALL NECESSARY SERVICING, REPAIR OR CORRECTION.

12. IN NO EVENT UNLESS REQUIRED BY APPLICABLE LAW OR AGREED TO IN WRITING WILL ANY COPYRIGHT HOLDER, OR ANY OTHER PARTY WHO MAY MODIFY AND/OR REDISTRIBUTE THE PROGRAM AS PERMITTED ABOVE, BE LIABLE TO YOU FOR DAMAGES, INCLUDING ANY GENERAL, SPECIAL, INCIDENTAL OR CONSEQUENTIAL DAMAGES ARISING OUT OF THE USE OR INABILITY TO USE THE PROGRAM (INCLUDING BUT NOT LIMITED TO LOSS OF DATA OR DATA BEING RENDERED INACCURATE OR LOSSES SUSTAINED BY YOU OR THIRD PARTIES OR A FAILURE OF THE PROGRAM TO OPERATE WITH ANY OTHER PROGRAMS), EVEN IF SUCH HOLDER OR OTHER PARTY HAS BEEN ADVISED OF THE POSSIBILITY OF SUCH DAMAGES.
END OF TERMS AND CONDITIONS





EndOfLicense;

    $lang->install_condition_title = '필수 설치조건을 확인하세요.';

    $lang->install_checklist_title = array(
            'php_version' => 'PHP Version',
            'permission' => '퍼미션',
            'xml' => 'XML 라이브러리',
            'iconv' => 'ICONV 라이브러리',
            'gd' => 'GD 라이브러리',
            'session' => 'Session.auto_start 설정',
        );

    $lang->install_checklist_desc = array(
            'php_version' => '[필수] PHP버전이 5.2.2일 경우 PHP의 버그로 인하여 설치되지 않습니다.',
            'permission' => '[필수] XE의 설치 경로 또는 ./files 디렉토리의 퍼미션이 707이어야 합니다.',
            'xml' => '[필수] XML통신을 위하여 XML 라이브러리가 필요합니다.',
            'session' => '[필수] XE에서 세션 사용을 위해 php.ini 설정의 session.auto_start=0 이어야 합니다.',
            'iconv' => 'UTF-8과 다른 언어셋의 변환을 위한 iconv설치가 필요합니다.',
            'gd' => '이미지변환 기능을 사용하기 위해 GD라이브러리가 설치되어 있어야 합니다.',
        );

    $lang->install_checklist_xml = 'XML라이브러리 설치';
    $lang->install_without_xml = 'xml 라이브러리가 설치되어 있지 않습니다.';
    $lang->install_checklist_gd = 'GD라이브러리 설치';
    $lang->install_without_gd  = '이미지 변환을 위한 GD 라이브러리가 설치되어 있지 않습니다.';
    $lang->install_checklist_gd = 'GD라이브러리 설치';
    $lang->install_without_iconv = '문자열을 처리하기 위한 iconv 라이브러리가 설치되어 있지 않습니다.';
    $lang->install_session_auto_start = 'php설정의 session.auto_start==1 이라 세션 처리에 문제가 발생할 수 있습니다.';
    $lang->install_permission_denied = '설치대상 디렉토리의 퍼미션이 707이 아닙니다.';

    $lang->cmd_agree_license = '라이선스에 동의합니다.';
    $lang->cmd_install_fix_checklist = '필수 설치조건을 설정하였습니다.';
    $lang->cmd_install_next = '설치를 진행합니다.';
    $lang->cmd_ignore = '무시';

    $lang->db_desc = array(
        'mysql' => 'mysql DB를 php의 mysql*()함수를 이용하여 사용합니다.<br />DB 파일은 myisam으로 생성되기에 트랜잭션이 이루어지지 않습니다.',
        'mysql_innodb' => 'mysql DB를 innodb를 이용하여 사용합니다.<br />innodb는 트랜잭션을 사용할 수 있습니다.',
        'sqlite2' => '파일로 데이터를 저장하는 sqlite2를 지원합니다.<br />설치 시 DB파일은 웹에서 접근할 수 없는 곳에 생성하여 주셔야 합니다.<br />(안정화 테스트가 되지 않았습니다.)',
        'sqlite3_pdo' => 'PHP의 PDO로 sqlite3를 지원합니다.<br />설치 시 DB파일은 웹에서 접근할 수 없는 곳에 생성하여 주셔야 합니다.',
        'cubrid' => 'CUBRID DB를 이용합니다. <a href="http://xe.xpressengine.net/?mid=wiki&entry=Cubrid+Database%EC%97%90+XE+%EC%84%A4%EC%B9%98%ED%95%98%EA%B8%B0" onclick="window.open(this.href);return false;" class="manual">manual</a>',
        'mssql' => 'MSSQL DB를 이용합니다.',
        'postgresql' => 'PostgreSql을 이용합니다.',
        'firebird' => 'Firebird를 이용합니다.<br />DB 생성 방법 (create database "/path/dbname.fdb" page_size=8192 default character set UTF-8;)',
    );

    $lang->form_title = 'DB &amp; 관리자 정보 입력';
    $lang->db_title = 'DB정보 입력';
    $lang->db_type = 'DB 종류';
    $lang->select_db_type = '사용하시려는 DB를 선택해주세요.';
    $lang->db_hostname = 'DB 호스트네임';
    $lang->db_port = 'DB Port';
    $lang->db_userid = 'DB 아이디';
    $lang->db_password = 'DB 비밀번호';
    $lang->db_database = 'DB 데이터베이스';
    $lang->db_database_file = 'DB 데이터베이스 파일';
    $lang->db_table_prefix = '테이블 머리말';

    $lang->admin_title = '관리자 정보';

    $lang->env_title = '환경 설정';
    $lang->use_optimizer = 'Optimizer 사용';
    $lang->about_optimizer = 'Optimizer를 사용하면 다수의 CSS/JS파일을 통합/압축 전송하여 매우 빠르게 사이트 접속이 가능하게 합니다.<br />다만 CSS나 JS에 따라서 문제가 생길 수 있습니다. 이때는 Optimizer 비활성화 하시면 정상적인 동작은 가능합니다.';
    $lang->use_rewrite = 'rewrite mod 사용';
    $lang->about_rewrite = '웹서버에서 rewrite mod를 지원하면 http://주소/?document_srl=123 같이 복잡한 주소를 http://주소/123과 같이 간단하게 줄일 수 있습니다.';
    $lang->time_zone = '표준 시간대';
    $lang->about_time_zone = '서버의 설정시간과 사용하려는 장소의 시간이 차이가 날 경우 표준 시간대를 지정하시면 표시되는 시간을 지정된 곳의 시간으로 사용하실 수 있습니다.';
    $lang->qmail_compatibility = 'Qmail 호환';
    $lang->about_qmail_compatibility = 'Qmail등 CRLF를 줄 구분자로 인식하지 못하는 MTA에서 메일이 발송되도록 합니다.';

    $lang->about_database_file = 'Sqlite는 파일에 데이터를 저장합니다. 데이터베이스 파일의 위치를 웹에서 접근할 수 없는 곳으로 하셔야 합니다.<br/><span style="color:red">데이터 파일은 707퍼미션 설정된 곳으로 지정해주세요.</span>';

    $lang->success_installed = '설치가 되었습니다.';
    $lang->success_updated = '업데이트가 되었습니다.';

    $lang->msg_cannot_proc = '설치 환경이 갖춰지지 않아 요청을 실행할 수가 없습니다.';
    $lang->msg_already_installed = '이미 설치가 되어 있습니다.';
    $lang->msg_dbconnect_failed = "DB접속 오류가 발생하였습니다.\nDB정보를 다시 확인해주세요.";
    $lang->msg_table_is_exists = "이미 DB에 테이블이 생성되어 있습니다.\nconfig파일을 재생성하였습니다.";
    $lang->msg_install_completed = "설치가 완료되었습니다.\n감사합니다.";
    $lang->msg_install_failed = '설치 파일 생성 시에 오류가 발생하였습니다.';
?>
