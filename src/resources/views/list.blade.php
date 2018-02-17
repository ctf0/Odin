{{-- styles --}}
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/bulma/0.6.1/css/bulma.min.css">
<link rel="stylesheet" href="{{ asset('assets/vendor/Odin/style.css') }}"/>

<section id="app" v-cloak>
    {{-- notifications --}}
    <div class="notif-container">
        @if (session('status'))
            <my-notification
                title="{{ session('title') ?: 'Success' }}"
                body="{{ session('status') }}"
                type="{{ session('type') ?: 'success' }}"
                duration="3">
            </my-notification>
        @endif

        <my-notification></my-notification>
    </div>

    {{-- Odin --}}
    <odin inline-template
        :translations="{{ json_encode(['ajax_fail' => trans('Odin::messages.ajax_fail')]) }}"
        :rev-list="{{ json_encode($revisions->pluck('id')) }}">

        <div>
            {{-- overlay --}}
            <div class="odin-animated fadeIn" :class="{'shade' : selected}" @click="toggleRev()"></div>

            <div class="columns">
                <div class="column revisions" ref="revisions">
                    {{-- list --}}
                    <table class="table is-hoverable is-narrow">
                        <tbody>
                            @foreach ($revisions as $rev)
                                @php
                                    $id = $rev->id;
                                    $user = $rev->user;
                                @endphp

                                <tr class="revisions-link"
                                    v-multi-ref="'rev-{{ $id }}'"
                                    @click="toggleRev({{ $id }})">

                                    {{-- user avatar --}}
                                    @if (isset($user->avatar))
                                        <td class="has-text-center">
                                            <figure class="image is-24x24">
                                                    <img src="{{ $user->avatar }}">
                                            </figure>
                                        </td>
                                    @endif

                                    {{-- user name --}}
                                    @if (isset($user->name))
                                        <td>{{ $user->name }}</td>
                                    @endif

                                    <td>
                                        {{ $rev->created_at->diffForHumans() }}
                                        <strong>"{{ $rev->created_at->format('F j, Y @ h:i A') }}"</strong>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    {{-- diff --}}
                    <div class="compare-page odin-animated fadeInUp"
                        v-show="selected"
                        ref="container">

                        {{-- close --}}
                        <div class="is-pulled-right">
                            <button class="delete" @click="toggleRev()"></button>
                        </div>

                        {{-- content --}}
                        <ul class="timeline">
                            @foreach ($revisions as $rev)
                                @php
                                    $id = $rev->id;
                                    $html = app('odin')->toHtml($rev);

                                    $class = $rev->event == 'created' ? 'is-link is-outlined' : 'is-success';
                                    $previewCheck = isset($template) && !in_array($rev->event, ['created', 'restored']);
                                @endphp

                                {{-- date --}}
                                <li id="{{ $id }}" class="timeline-header" v-multi-ref="'rev-{{ $id }}'">
                                    <button class="tag is-rounded is-medium is-black revisions-link"
                                        @click.stop="updateRev({{ $id }}), goTo('{{ $id }}')">
                                        {{ $rev->created_at->diffForHumans() }}
                                    </button>
                                </li>

                                {{-- data --}}
                                <li class="timeline-item" v-multi-ref="'rev-{{ $id }}'">
                                    <div class="timeline-marker is-icon"
                                        :class="{'is-link' : selected == '{{ $id }}'}">
                                        <template v-if="selected == '{{ $id }}'">
                                            <icon name="flag" scale="0.75"></icon>
                                        </template>
                                    </div>

                                    <div class="timeline-content">
                                        <div class="heading">
                                            <p><span class="title">{{ $rev->event }}</span></p>
                                            <p>
                                                <span class="subtitle is-6">
                                                    {{ $rev->created_at->format('F j, Y @ h:i A') }}
                                                </span>
                                            </p>
                                            <p>
                                                <small class="subtitle is-6">By</small>
                                                <span class="subtitle is-5">{{ $rev->user->name }}</span>
                                            </p>
                                        </div>

                                        <div>
                                            {{-- body --}}
                                            <section class="compare-page__body">
                                                @if ($html)
                                                    {!! $html !!}
                                                @else
                                                    <p class="title is-5 is-info is-marginless notification">
                                                        {{ trans('Odin::messages.no_diff') }}
                                                    </p>
                                                @endif
                                            </section>

                                            {{-- ops --}}
                                            @if (count($revisions) > 1)
                                                <div class="compare-page__footer">

                                                    @if ($rev->event == 'created')
                                                        <p class="title is-6">{{ trans('Odin::messages.reset_data') }}</p>
                                                    @endif

                                                    <div class="level">
                                                        @if ($html)
                                                            {{-- preview --}}
                                                            <div class="level-left">
                                                                @if ($previewCheck)
                                                                    <div class="level-item">
                                                                        <form action="{{ route('odin.preview', $id) }}"
                                                                            method="POST"
                                                                            target="_blank">
                                                                            {{ csrf_field() }}
                                                                            <input type="hidden" name="template" value="{{ $template }}">
                                                                            <button class="button is-link is-outlined">
                                                                                {{ trans('Odin::messages.preview') }}
                                                                            </button>
                                                                        </form>
                                                                    </div>
                                                                @endif
                                                            </div>

                                                            <div class="level-right">
                                                                <div class="level-item">
                                                                    @if ($rev->event == 'deleted')
                                                                        @if ($loop->first)
                                                                            {{-- restore softDelete --}}
                                                                            <form action="{{ route('odin.restore.soft', $id) }}"
                                                                                method="POST">
                                                                                {{ method_field('PUT') }}
                                                                                {{ csrf_field() }}
                                                                                <button class="button is-success">
                                                                                    {{ trans('Odin::messages.res_model') }}
                                                                                </button>
                                                                            </form>
                                                                        @endif

                                                                    @else
                                                                        @if ($rev->event !== 'restored')
                                                                            {{-- restore normal --}}
                                                                            <form action="{{ route('odin.restore', $id) }}"
                                                                                method="POST">
                                                                                {{ csrf_field() }}
                                                                                <button class="button {{ $class }}">
                                                                                    {{ trans('Odin::messages.res') }}
                                                                                </button>
                                                                            </form>
                                                                        @endif

                                                                    @endif
                                                                </div>

                                                                {{-- remove revision --}}
                                                                <div class="level-item">
                                                                    <form action="{{ route('odin.remove', $id) }}"
                                                                        method="POST"
                                                                        data-id="{{ $id }}"
                                                                        @submit.prevent="removeRev($event)">
                                                                        {{ method_field('DELETE') }}
                                                                        {{ csrf_field() }}
                                                                        <button class="button is-danger">
                                                                            {{ trans('Odin::messages.del') }}
                                                                        </button>
                                                                    </form>
                                                                </div>
                                                            </div>

                                                        @else
                                                            <div class="level-left"></div>
                                                            <div class="level-right">
                                                                {{-- remove revision --}}
                                                                <div class="level-item">
                                                                    <form action="{{ route('odin.remove', $id) }}"
                                                                        method="POST"
                                                                        data-id="{{ $id }}"
                                                                        @submit.prevent="removeRev($event)">
                                                                        {{ csrf_field() }}
                                                                        {{ method_field('DELETE') }}
                                                                        <button class="button is-danger">
                                                                            {{ trans('Odin::messages.del') }}
                                                                        </button>
                                                                    </form>
                                                                </div>
                                                            </div>

                                                        @endif
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                </div>
            </div>
        </div>
    </odin>

</section>

{{-- Footer --}}
<script src="{{ asset('path/to/app.js') }}"></script>
